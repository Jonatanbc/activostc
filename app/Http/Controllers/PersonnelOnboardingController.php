<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\OnboardingRequest;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Gestión de ingreso del personal": a friendly, progressive wizard where a boss
 * requests the equipment, components and platforms for a new hire. IT reviews the
 * resulting request and performs the real provisioning.
 */
class PersonnelOnboardingController extends Controller
{
    /** Assets in inventory that can be assigned (same rule as the availability module). */
    private function availableAssets()
    {
        $inventoryStatusIds = Statuslabel::whereNull('deleted_at')
            ->where(fn ($q) => $q->where('deployable', 1)->orWhere('pending', 1))
            ->pluck('id');

        return Asset::whereNull('assets.assigned_to')
            ->whereIn('assets.status_id', $inventoryStatusIds->isEmpty() ? [0] : $inventoryStatusIds->all())
            ->with('model')
            ->orderBy('asset_tag')
            ->get();
    }

    /** Accessories with at least one unit still available. */
    private function accessoriesInStock()
    {
        return Accessory::withCount('checkouts')
            ->orderBy('name')
            ->get()
            ->filter(fn ($a) => ($a->qty - $a->checkouts_count) > 0)
            ->values();
    }

    /**
     * The onboarding wizard.
     */
    public function create()
    {
        $this->authorize('equipos.availability');

        return view('onboarding/create')
            ->with('availableAssets', $this->availableAssets())
            ->with('accessories', $this->accessoriesInStock())
            ->with('users', User::whereNull('deleted_at')->orderBy('first_name')->get())
            ->with('platforms', OnboardingRequest::PLATFORMS)
            ->with('otmRoles', OnboardingRequest::OTM_ROLES);
    }

    /**
     * Store the onboarding request for IT to process.
     */
    public function store(Request $request)
    {
        $this->authorize('equipos.availability');

        $data = $request->validate([
            'position' => 'required|string|max:191',
            'employee_name' => 'required|string|max:191',
            'entry_type' => 'required|in:nuevo,reemplazo',
            'replaces_user_id' => 'required_if:entry_type,reemplazo|nullable|integer|exists:users,id',
            'asset_id' => 'nullable|integer|exists:assets,id',
            'accessories' => 'nullable|array',
            'accessories.*' => 'integer|exists:accessories,id',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|in:'.implode(',', OnboardingRequest::PLATFORMS),
            'otm_roles' => 'nullable|array',
            'otm_roles.*' => 'string|in:'.implode(',', OnboardingRequest::OTM_ROLES),
            'notes' => 'nullable|string',
        ]);

        // Only a replacement carries a "replaces whom".
        if ($data['entry_type'] !== OnboardingRequest::ENTRY_REPLACEMENT) {
            $data['replaces_user_id'] = null;
        }

        // OTM roles only make sense if OTM is among the requested platforms.
        if (! in_array('OTM', $data['platforms'] ?? [], true)) {
            $data['otm_roles'] = null;
        }

        $onboarding = new OnboardingRequest($data);
        $onboarding->requested_by = auth()->id();
        $onboarding->status = OnboardingRequest::STATUS_PENDING;
        $onboarding->save();

        return redirect()->route('onboarding.index')
            ->with('success', trans('admin/onboarding/general.created'));
    }

    /**
     * IT review list.
     */
    public function index(Request $request)
    {
        $this->authorize('equipos.availability');

        $status = $request->input('status', 'pending');

        $query = OnboardingRequest::with(['asset.model', 'replacesUser', 'requestedBy', 'processedBy'])
            ->orderByDesc('created_at');

        if (in_array($status, OnboardingRequest::STATUSES, true)) {
            $query->where('status', $status);
        }

        // Resolve the requested accessory ids to names for display.
        $requests = $query->get();
        $accessoryNames = Accessory::whereIn('id', $requests->flatMap(fn ($r) => $r->accessories ?? [])->unique()->all())
            ->pluck('name', 'id');

        return view('onboarding/index')
            ->with('requests', $requests)
            ->with('status', $status)
            ->with('accessoryNames', $accessoryNames)
            ->with('counts', [
                'pending' => OnboardingRequest::where('status', OnboardingRequest::STATUS_PENDING)->count(),
                'approved' => OnboardingRequest::where('status', OnboardingRequest::STATUS_APPROVED)->count(),
                'fulfilled' => OnboardingRequest::where('status', OnboardingRequest::STATUS_FULFILLED)->count(),
                'rejected' => OnboardingRequest::where('status', OnboardingRequest::STATUS_REJECTED)->count(),
            ]);
    }

    /**
     * IT changes the status of an onboarding request.
     */
    public function updateStatus(Request $request, OnboardingRequest $onboarding)
    {
        $this->authorize('update', Asset::class);

        $request->validate(['status' => 'required|in:pending,approved,rejected,fulfilled']);

        $onboarding->status = $request->input('status');
        $onboarding->processed_by = auth()->id();
        $onboarding->processed_at = now();
        $onboarding->save();

        return redirect()->back()->with('success', trans('admin/onboarding/general.updated'));
    }

    public function destroy(OnboardingRequest $onboarding)
    {
        $this->authorize('delete', Asset::class);
        $onboarding->delete();

        return redirect()->back()->with('success', trans('admin/onboarding/general.deleted'));
    }

    /**
     * AJAX: assets currently assigned to a user (to reassign on a replacement).
     */
    public function userAssets(User $user): JsonResponse
    {
        $this->authorize('equipos.availability');

        $assets = Asset::where('assigned_to', $user->id)
            ->where('assigned_type', User::class)
            ->with('model')
            ->orderBy('asset_tag')
            ->get()
            ->map(fn (Asset $a) => [
                'id' => $a->id,
                'text' => ($a->asset_tag ? $a->asset_tag.' · ' : '').(optional($a->model)->name ?: $a->name),
            ]);

        return response()->json(['assets' => $assets]);
    }
}
