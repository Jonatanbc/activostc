<?php

namespace App\Http\Controllers;

use App\Models\AssetDamage;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseRequestsController extends Controller
{
    /**
     * List all purchase requests (each groups several damages).
     */
    public function index()
    {
        $this->authorize('view', \App\Models\Asset::class);

        $requests = PurchaseRequest::with('supplier', 'damages')
            ->withCount('damages')
            ->orderByDesc('id')
            ->paginate(20);

        $stats = [
            'total' => PurchaseRequest::count(),
            'open' => PurchaseRequest::where('status', PurchaseRequest::STATUS_OPEN)->count(),
            'total_cost' => AssetDamage::whereNotNull('purchase_request_id')->sum('cost'),
        ];

        return view('purchase-requests/index')->with('requests', $requests)->with('stats', $stats);
    }

    /**
     * Create a purchase request grouping the selected damages.
     */
    public function store(Request $request)
    {
        $this->authorize('update', \App\Models\Asset::class);

        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $pr = PurchaseRequest::create([
            'status' => PurchaseRequest::STATUS_OPEN,
            'created_by' => auth()->id(),
        ]);

        AssetDamage::whereIn('id', $data['ids'])->update([
            'purchase_request_id' => $pr->id,
            'status' => AssetDamage::STATUS_PURCHASE_REQUEST,
        ]);

        return redirect()->route('purchase-requests.show', $pr->id)
            ->with('success', trans('admin/damages/message.pr.created'));
    }

    /**
     * Show a purchase request with its grouped damages and quotation total.
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('view', \App\Models\Asset::class);

        $purchaseRequest->load(['damages.asset.model', 'damages.damageType', 'supplier', 'createdBy']);

        return view('purchase-requests/show')
            ->with('pr', $purchaseRequest)
            ->with('suppliers', Supplier::orderBy('name')->get());
    }

    /**
     * Update supplier, status and notes of a purchase request.
     */
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->authorize('update', \App\Models\Asset::class);

        $data = $request->validate([
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'status' => 'required|in:open,quoted,ordered,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $purchaseRequest->update($data);

        return redirect()->route('purchase-requests.show', $purchaseRequest->id)
            ->with('success', trans('admin/damages/message.pr.updated'));
    }

    /**
     * Remove a single damage from the purchase request.
     */
    public function removeItem(PurchaseRequest $purchaseRequest, AssetDamage $damage)
    {
        $this->authorize('update', \App\Models\Asset::class);

        if ($damage->purchase_request_id == $purchaseRequest->id) {
            $damage->update([
                'purchase_request_id' => null,
                'status' => AssetDamage::STATUS_QUOTED,
            ]);
        }

        return redirect()->route('purchase-requests.show', $purchaseRequest->id)
            ->with('success', trans('admin/damages/message.pr.item_removed'));
    }

    /**
     * Delete a purchase request and release its damages.
     */
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('update', \App\Models\Asset::class);

        AssetDamage::where('purchase_request_id', $purchaseRequest->id)->update([
            'purchase_request_id' => null,
            'status' => AssetDamage::STATUS_QUOTED,
        ]);

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', trans('admin/damages/message.pr.deleted'));
    }

    /**
     * Printable quotation for the purchase request.
     */
    public function print(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('view', \App\Models\Asset::class);

        // Generating the quotation moves an open request to "quoted"
        // (only a user who can update assets, and never downgrading a further status).
        if ($purchaseRequest->status === PurchaseRequest::STATUS_OPEN
            && auth()->user()->can('update', \App\Models\Asset::class)) {
            $purchaseRequest->update(['status' => PurchaseRequest::STATUS_QUOTED]);
        }

        $purchaseRequest->load(['damages.asset.model', 'damages.damageType', 'supplier', 'createdBy']);

        return view('purchase-requests/print')->with('pr', $purchaseRequest);
    }
}
