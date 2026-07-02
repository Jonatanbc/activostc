<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\DamagesTransformer;
use App\Models\Asset;
use App\Models\AssetDamage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DamagesController extends Controller
{
    /**
     * Datatable feed for damages (per asset, or the full per-unit report).
     */
    public function index(Request $request)
    {
        $this->authorize('view', Asset::class);

        $damages = AssetDamage::with('asset.model', 'asset.assignedTo', 'asset.location', 'damageType', 'supplier', 'reportedBy', 'images');

        if ($request->filled('asset_id')) {
            $damages->where('asset_damages.asset_id', $request->input('asset_id'));
        }

        if ($request->filled('status')) {
            $damages->where('asset_damages.status', $request->input('status'));
        }

        // Filter by the asset's model (used by the report).
        if ($request->filled('model_id')) {
            $damages->whereHas('asset', function ($q) use ($request) {
                $q->where('model_id', $request->input('model_id'));
            });
        }

        // "pending" = still needs a repair (reported/quoted).
        if ($request->input('only_pending') == 'true') {
            $damages->whereIn('asset_damages.status', AssetDamage::PENDING_STATUSES);
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), ['id', 'quantity', 'cost', 'status', 'reported_at'])
            ? $request->input('sort') : 'created_at';

        $total = $damages->count();
        $damages->orderBy($sort, $order)->skip($offset)->take($limit);

        if ($request->input('report') === 'flat') {
            return (new DamagesTransformer)->transformDamagesReport($damages->get(), $total);
        }

        return (new DamagesTransformer)->transformDamages($damages->get(), $total);
    }

    /**
     * Report aggregated by asset model + damage type: how many spare parts and total cost
     * are needed to refurbish each model (e.g. all HP 240 G6).
     */
    public function byModel(Request $request)
    {
        $this->authorize('view', Asset::class);

        $query = DB::table('asset_damages')
            ->join('assets', 'assets.id', '=', 'asset_damages.asset_id')
            ->join('models', 'models.id', '=', 'assets.model_id')
            ->join('damage_types', 'damage_types.id', '=', 'asset_damages.damage_type_id')
            ->whereNull('asset_damages.deleted_at')
            ->whereNull('assets.deleted_at')
            ->select(
                'models.id as model_id',
                'models.name as model',
                'damage_types.name as damage_type',
                DB::raw('SUM(asset_damages.quantity) as parts_needed'),
                DB::raw('COUNT(DISTINCT asset_damages.asset_id) as affected_assets'),
                DB::raw('SUM(asset_damages.cost) as total_cost')
            )
            ->groupBy('models.id', 'models.name', 'damage_types.name')
            ->orderBy('models.name')
            ->orderBy('damage_types.name');

        if ($request->input('only_pending', 'true') === 'true') {
            $query->whereIn('asset_damages.status', AssetDamage::PENDING_STATUSES);
        }

        if ($request->filled('model_id')) {
            $query->where('models.id', $request->input('model_id'));
        }

        $rows = $query->get()->map(function ($r) {
            return [
                'model_id' => (int) $r->model_id,
                'model' => e($r->model),
                'damage_type' => e($r->damage_type),
                'parts_needed' => (int) $r->parts_needed,
                'affected_assets' => (int) $r->affected_assets,
                'total_cost' => Helper::formatCurrencyOutput($r->total_cost),
            ];
        });

        return (new \App\Http\Transformers\DatatablesTransformer)->transformDatatables($rows->toArray(), $rows->count());
    }

    public function show($id)
    {
        $this->authorize('view', Asset::class);
        $damage = AssetDamage::with('asset.model', 'asset.assignedTo', 'asset.location', 'damageType', 'supplier', 'reportedBy', 'images')->findOrFail($id);

        return (new DamagesTransformer)->transformDamage($damage);
    }

    public function destroy($id)
    {
        $damage = AssetDamage::findOrFail($id);
        $this->authorize('delete', Asset::class);
        $damage->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/damages/message.delete.success')));
    }
}
