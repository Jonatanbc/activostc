<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDamage;
use App\Models\DamageImage;
use App\Models\DamageType;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DamagesController extends Controller
{
    /**
     * Standalone Damages module: summary cards + full listing with bulk actions.
     */
    public function index()
    {
        $this->authorize('view', Asset::class);

        $base = AssetDamage::query();

        $stats = [
            'damaged_components' => (int) (clone $base)->sum('quantity'),
            'avg_cost' => (clone $base)->whereNotNull('cost')->avg('cost'),
            'total_cost' => (clone $base)->sum('cost'),
            'purchase_requests' => (int) (clone $base)->where('status', AssetDamage::STATUS_PURCHASE_REQUEST)->count(),
        ];

        // Count per component (damage type): e.g. Teclado 2, Pantalla 3, ...
        $byType = \Illuminate\Support\Facades\DB::table('asset_damages')
            ->join('damage_types', 'damage_types.id', '=', 'asset_damages.damage_type_id')
            ->whereNull('asset_damages.deleted_at')
            ->groupBy('damage_types.id', 'damage_types.name')
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('SUM(asset_damages.quantity)'))
            ->select('damage_types.name', \Illuminate\Support\Facades\DB::raw('SUM(asset_damages.quantity) as qty'))
            ->get();

        $schedules = \App\Models\ReportEmailSchedule::where('report_key', 'damages_list')
            ->orderByDesc('id')->get();

        return view('damages/index')->with('stats', $stats)->with('byType', $byType)->with('schedules', $schedules);
    }

    /**
     * Show the form to register a new damage for an asset.
     */
    public function create(Request $request)
    {
        $this->authorize('update', Asset::class);

        $asset = Asset::findOrFail($request->input('asset_id'));

        return view('damages/edit')
            ->with('item', new AssetDamage)
            ->with('asset', $asset)
            ->with('damage_types', DamageType::orderBy('name')->get())
            ->with('suppliers', Supplier::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $this->authorize('update', Asset::class);

        $request->validate([
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'nullable|mimes:png,gif,jpg,jpeg,webp,bmp|max:8192',
        ]);

        $damage = new AssetDamage;
        $damage->asset_id = $request->input('asset_id');
        $damage->damage_type_id = $request->input('damage_type_id');
        $damage->quantity = (int) $request->input('quantity', 1);
        $damage->status = $request->input('status', AssetDamage::STATUS_REPORTED);
        $damage->supplier_id = $request->input('supplier_id') ?: null;
        $damage->erp_purchase_code = $request->input('erp_purchase_code') ?: null;
        $damage->reported_by = auth()->id();
        $damage->reported_at = $request->input('reported_at') ?: now()->format('Y-m-d');
        $damage->repaired_at = $request->input('repaired_at') ?: null;
        $damage->notes = $request->input('notes');
        $damage->created_by = auth()->id();

        // Cost defaults to (standard cost x quantity) when left blank, otherwise the real quote.
        $cost = $request->input('cost');
        if ($cost === null || $cost === '') {
            $type = DamageType::find($damage->damage_type_id);
            $cost = $type && $type->default_cost ? $type->default_cost * $damage->quantity : null;
        }
        $damage->cost = $cost;

        if ($damage->save()) {
            $this->handleDamagePhotos($request, $damage);

            if ($request->boolean('modal')) {
                return view('damages.modal-done');
            }

            return redirect()->route('hardware.show', $damage->asset_id)
                ->with('success', trans('admin/damages/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($damage->getErrors());
    }

    public function edit(AssetDamage $damage)
    {
        $this->authorize('update', Asset::class);

        return view('damages/edit')
            ->with('item', $damage->load('images'))
            ->with('asset', $damage->asset)
            ->with('damage_types', DamageType::orderBy('name')->get())
            ->with('suppliers', Supplier::orderBy('name')->get());
    }

    public function update(Request $request, AssetDamage $damage)
    {
        $this->authorize('update', Asset::class);

        $request->validate([
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'nullable|mimes:png,gif,jpg,jpeg,webp,bmp|max:8192',
        ]);

        $damage->damage_type_id = $request->input('damage_type_id');
        $damage->quantity = (int) $request->input('quantity', 1);
        $damage->cost = $request->input('cost');
        $damage->status = $request->input('status');
        $damage->supplier_id = $request->input('supplier_id') ?: null;
        $damage->erp_purchase_code = $request->input('erp_purchase_code') ?: null;
        $damage->reported_at = $request->input('reported_at') ?: null;
        $damage->repaired_at = $request->input('repaired_at') ?: null;
        $damage->notes = $request->input('notes');

        if ($damage->save()) {
            $this->deleteDamagePhotos($request, $damage);
            $this->handleDamagePhotos($request, $damage);

            if ($request->boolean('modal')) {
                return view('damages.modal-done');
            }

            return redirect()->route('hardware.show', $damage->asset_id)
                ->with('success', trans('admin/damages/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($damage->getErrors());
    }

    public function destroy(AssetDamage $damage)
    {
        $this->authorize('delete', Asset::class);
        $asset_id = $damage->asset_id;

        // Remove attached photos (files + rows) before deleting the damage.
        foreach ($damage->images as $image) {
            Storage::disk('public')->delete(DamageImage::UPLOAD_PATH.'/'.$image->filename);
            $image->delete();
        }

        $damage->delete();

        return redirect()->route('hardware.show', $asset_id)
            ->with('success', trans('admin/damages/message.delete.success'));
    }

    /**
     * Store any uploaded photos for a damage record (resized, on the public disk).
     */
    private function handleDamagePhotos(Request $request, AssetDamage $damage): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $path = DamageImage::UPLOAD_PATH;
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }

        // Full-resolution phone photos (e.g. 4032x3024) need well over the default
        // 128M to decode into a GD bitmap; bump the limit so processing doesn't die
        // mid-request after the damage row was already saved.
        @ini_set('memory_limit', '512M');

        foreach ($request->file('photos') as $photo) {
            if (! $photo) {
                continue;
            }

            $ext = $photo->guessExtension() ?: $photo->getClientOriginalExtension();
            $file_name = 'damage-'.$damage->id.'-'.Str::random(10).'.'.$ext;

            try {
                // Resize down to a sensible max width, keeping aspect ratio.
                $upload = Image::make($photo->getRealPath())
                    ->resize(1200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->orientate();
                Storage::disk('public')->put($path.'/'.$file_name, (string) $upload->encode());
            } catch (\Throwable $e) {
                // Fall back to storing the original if it can't be processed.
                Log::debug($e);
                Storage::disk('public')->put($path.'/'.$file_name, file_get_contents($photo));
            }

            DamageImage::create([
                'asset_damage_id' => $damage->id,
                'filename' => $file_name,
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Delete the photos the user selected for removal (edit form only).
     */
    private function deleteDamagePhotos(Request $request, AssetDamage $damage): void
    {
        if (! $request->filled('delete_photos')) {
            return;
        }

        $images = DamageImage::where('asset_damage_id', $damage->id)
            ->whereIn('id', (array) $request->input('delete_photos'))
            ->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete(DamageImage::UPLOAD_PATH.'/'.$image->filename);
            $image->delete();
        }
    }

    /**
     * Per-unit damages report (every damage, filterable, with cost totals).
     */
    public function report()
    {
        $this->authorize('view', Asset::class);

        return view('damages/report');
    }

    /**
     * Aggregated report: spare parts needed and total cost per asset model.
     */
    public function reportByModel()
    {
        $this->authorize('view', Asset::class);

        return view('damages/report-by-model');
    }

    /**
     * Pivot report: one row per model, one column per damage type (with quantities)
     * plus the total cost per model. e.g. "240 G6 | Teclado 1 | Pantalla 3 | Board 1 | $..."
     */
    public function reportByModelMatrix(Request $request)
    {
        $this->authorize('view', Asset::class);

        $onlyPending = $request->input('only_pending') === 'true';
        $matrix = AssetDamage::byModelMatrix($onlyPending);

        return view('damages/report-by-model-matrix')
            ->with('types', $matrix['types'])
            ->with('rows', $matrix['rows'])
            ->with('colTotals', $matrix['colTotals'])
            ->with('grandQty', $matrix['grandQty'])
            ->with('grandCost', $matrix['grandCost'])
            ->with('onlyPending', $onlyPending)
            ->with('schedules', \App\Models\ReportEmailSchedule::where('report_key', 'damages_by_model')
                ->orderByDesc('id')->get());
    }

    /**
     * Send the "damages by model" report by email right now.
     */
    public function emailByModel(Request $request)
    {
        $this->authorize('view', Asset::class);

        $data = $this->validateReportEmail($request);

        [$level, $message] = $this->sendMail(new \App\Mail\DamageReportMail($data['only_pending']), $data['recipients']);

        return redirect()->route('reports/damages_by_model_summary', $data['only_pending'] ? ['only_pending' => 'true'] : [])
            ->with($level, $message);
    }

    /**
     * Send a report/list mailable (same medium as the checkout/acta email: a Mailable
     * dispatched via Mail::to()->send() through the app's configured mailer).
     * Returns [flashLevel, message] so the UI is honest about what actually happened.
     */
    private function sendMail(\Illuminate\Mail\Mailable $mailable, array $recipients): array
    {
        try {
            \Illuminate\Support\Facades\Mail::to($recipients)->send($mailable);
        } catch (\Throwable $e) {
            Log::error('Damage report email failed: '.$e->getMessage());

            return ['error', trans('admin/damages/message.email.failed', ['error' => $e->getMessage()])];
        }

        // MAIL_MAILER=log/array means nothing is actually delivered — don't claim success.
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return ['warning', trans('admin/damages/message.email.log_mode')];
        }

        return ['success', trans('admin/damages/message.email.sent')];
    }

    /**
     * Create a recurring email schedule for the "damages by model" report.
     */
    public function scheduleByModel(Request $request)
    {
        $this->authorize('view', Asset::class);

        $data = $this->validateReportEmail($request);
        $request->validate([
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'send_day' => 'required|integer|between:0,6',
            'send_time' => 'required|date_format:H:i',
        ]);

        $schedule = \App\Models\ReportEmailSchedule::create([
            'report_key' => 'damages_by_model',
            'recipients' => implode(',', $data['recipients']),
            'frequency' => $request->input('frequency'),
            'send_day' => (int) $request->input('send_day'),
            'send_time' => $request->input('send_time'),
            'only_pending' => $data['only_pending'],
            'is_active' => true,
            'created_by' => auth()->id(),
            'next_run_at' => \App\Models\ReportEmailSchedule::computeFirstRun(
                (int) $request->input('send_day'),
                $request->input('send_time'),
            ),
        ]);

        // Send a first copy right away so the recipient sees it works (best effort).
        [$level] = $this->sendMail(new \App\Mail\DamageReportMail($data['only_pending']), $data['recipients']);
        $schedule->update(['last_sent_at' => now()]);

        // The schedule was created regardless; warn if the immediate copy only went to the log.
        if ($level === 'success') {
            return redirect()->route('reports/damages_by_model_summary')
                ->with('success', trans('admin/damages/message.email.scheduled'));
        }

        return redirect()->route('reports/damages_by_model_summary')
            ->with('warning', trans('admin/damages/message.email.scheduled_log'));
    }

    public function deleteSchedule(\App\Models\ReportEmailSchedule $schedule)
    {
        $this->authorize('view', Asset::class);
        $schedule->delete();

        return redirect()->back()
            ->with('success', trans('admin/damages/message.email.schedule_deleted'));
    }

    /**
     * Email the damages list (with cost total) right now.
     */
    public function emailList(Request $request)
    {
        $this->authorize('view', Asset::class);

        $data = $this->validateReportEmail($request);

        [$level, $message] = $this->sendMail(new \App\Mail\DamagesListMail($data['only_pending']), $data['recipients']);

        return redirect()->route('damages.list')->with($level, $message);
    }

    /**
     * Schedule recurring emails of the damages list.
     */
    public function scheduleList(Request $request)
    {
        $this->authorize('view', Asset::class);

        $data = $this->validateReportEmail($request);
        $request->validate([
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'send_day' => 'required|integer|between:0,6',
            'send_time' => 'required|date_format:H:i',
        ]);

        $schedule = \App\Models\ReportEmailSchedule::create([
            'report_key' => 'damages_list',
            'recipients' => implode(',', $data['recipients']),
            'frequency' => $request->input('frequency'),
            'send_day' => (int) $request->input('send_day'),
            'send_time' => $request->input('send_time'),
            'only_pending' => $data['only_pending'],
            'is_active' => true,
            'created_by' => auth()->id(),
            'next_run_at' => \App\Models\ReportEmailSchedule::computeFirstRun(
                (int) $request->input('send_day'),
                $request->input('send_time'),
            ),
        ]);

        [$level] = $this->sendMail(new \App\Mail\DamagesListMail($data['only_pending']), $data['recipients']);
        $schedule->update(['last_sent_at' => now()]);

        $key = $level === 'success' ? 'admin/damages/message.email.scheduled' : 'admin/damages/message.email.scheduled_log';

        return redirect()->route('damages.list')->with($level === 'success' ? 'success' : 'warning', trans($key));
    }

    /**
     * Validate the recipient list (comma/semicolon separated) and pending flag.
     */
    private function validateReportEmail(Request $request): array
    {
        $request->validate(['recipients' => 'required|string']);

        $emails = collect(preg_split('/[,;\s]+/', $request->input('recipients'), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->unique()
            ->values();

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['recipients' => $emails->all()],
            ['recipients' => 'required|array|min:1', 'recipients.*' => 'email']
        );
        $validator->validate();

        return [
            'recipients' => $emails->all(),
            'only_pending' => $request->input('only_pending') === 'true' || $request->boolean('only_pending'),
        ];
    }
}
