<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetRequest;
use Illuminate\Http\Request;

class AssetRequestController extends Controller
{
    /**
     * List asset assignment requests for IT to review.
     */
    public function index(Request $request)
    {
        $this->authorize('view', Asset::class);

        $status = $request->input('status', 'pending');

        $query = AssetRequest::with(['asset.model', 'requestedBy', 'processedBy'])
            ->orderByDesc('created_at');

        if (in_array($status, AssetRequest::STATUSES, true)) {
            $query->where('status', $status);
        }

        return view('asset-requests/index')
            ->with('requests', $query->get())
            ->with('status', $status)
            ->with('counts', [
                'pending' => AssetRequest::where('status', AssetRequest::STATUS_PENDING)->count(),
                'approved' => AssetRequest::where('status', AssetRequest::STATUS_APPROVED)->count(),
                'fulfilled' => AssetRequest::where('status', AssetRequest::STATUS_FULFILLED)->count(),
                'rejected' => AssetRequest::where('status', AssetRequest::STATUS_REJECTED)->count(),
            ]);
    }

    /**
     * Store a new assignment request (the questionnaire) from the availability module.
     */
    public function store(Request $request)
    {
        $this->authorize('equipos.availability');

        $data = $request->validate([
            'asset_id' => 'required|integer|exists:assets,id',
            'assignee_name' => 'required|string|max:191',
            'assignee_id_number' => 'nullable|string|max:100',
            'assignee_position' => 'nullable|string|max:191',
            'assignee_location' => 'nullable|string|max:191',
            'account_type' => 'nullable|in:nueva,reemplazo',
            'replaces_person' => 'nullable|string|max:191',
            'needed_at' => 'nullable|date',
            'justification' => 'nullable|string',
        ]);

        // "Replaces whom" only makes sense for a replacement account.
        if (($data['account_type'] ?? null) !== AssetRequest::ACCOUNT_REPLACEMENT) {
            $data['replaces_person'] = null;
        }

        $assetRequest = new AssetRequest($data);
        $assetRequest->requested_by = auth()->id();
        $assetRequest->status = AssetRequest::STATUS_PENDING;
        $assetRequest->save();

        return redirect()->route('assets.availability')
            ->with('success', trans('admin/damages/general.request_created'));
    }

    /**
     * IT changes the status of a request (approve / reject / mark fulfilled).
     */
    public function updateStatus(Request $request, AssetRequest $assetRequest)
    {
        $this->authorize('update', Asset::class);

        $request->validate(['status' => 'required|in:pending,approved,rejected,fulfilled']);

        $assetRequest->status = $request->input('status');
        $assetRequest->processed_by = auth()->id();
        $assetRequest->processed_at = now();
        $assetRequest->save();

        return redirect()->back()
            ->with('success', trans('admin/damages/general.request_updated'));
    }

    public function destroy(AssetRequest $assetRequest)
    {
        $this->authorize('delete', Asset::class);
        $assetRequest->delete();

        return redirect()->back()
            ->with('success', trans('admin/damages/general.request_deleted'));
    }
}
