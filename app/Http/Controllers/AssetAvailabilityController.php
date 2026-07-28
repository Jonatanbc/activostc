<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDamage;
use App\Models\DamageType;

/**
 * "Disponibilidad de equipos": assignable (RTD) assets shown with a cost-weighted
 * health bar derived from their pending damages, plus a per-component status.
 */
class AssetAvailabilityController extends Controller
{
    public function index()
    {
        $this->authorize('equipos.availability');

        $damageTypes = DamageType::orderBy('name')->get();
        $criticalTypes = $damageTypes->where('is_critical', true);
        $nonCriticalTypes = $damageTypes->where('is_critical', false);

        // Cost totals used to scale the health bar within each band.
        $critTotal = max(1.0, (float) $criticalTypes->sum(fn ($t) => (float) $t->default_cost));
        $nonCritTotal = max(1.0, (float) $nonCriticalTypes->sum(fn ($t) => (float) $t->default_cost));
        $criticalIds = $criticalTypes->pluck('id')->all();

        // Equipment offered for request: only assets explicitly flagged as
        // requestable ("puede solicitarse"), not assigned to anyone, in a
        // deployable or pending status (excludes archived/stolen). Broader than
        // RTD so damaged units under evaluation still show up.
        $inventoryStatusIds = \App\Models\Statuslabel::whereNull('deleted_at')
            ->where(fn ($q) => $q->where('deployable', 1)->orWhere('pending', 1))
            ->pluck('id');

        $assets = Asset::where('assets.requestable', 1)
            ->whereNull('assets.assigned_to')
            ->whereIn('assets.status_id', $inventoryStatusIds->isEmpty() ? [0] : $inventoryStatusIds->all())
            ->with(['model.category', 'location'])
            ->orderBy('asset_tag')
            ->get();

        // Pending (unrepaired) damages for those assets, grouped by asset.
        $damages = AssetDamage::with(['damageType', 'images'])
            ->whereIn('asset_id', $assets->pluck('id'))
            ->where('status', '!=', AssetDamage::STATUS_REPAIRED)
            ->get()
            ->groupBy('asset_id');

        // Most recent journal note ("note added") per asset, if any.
        $lastNotes = \App\Models\Actionlog::where('item_type', Asset::class)
            ->where('action_type', 'note added')
            ->whereIn('item_id', $assets->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get(['id', 'item_id', 'note', 'created_at'])
            ->groupBy('item_id')
            ->map(fn ($logs) => $logs->first());

        $rows = $assets->map(function (Asset $asset) use ($damages, $lastNotes, $damageTypes, $criticalIds, $critTotal, $nonCritTotal) {
            $assetDamages = $damages->get($asset->id, collect());
            $lastNote = $lastNotes->get($asset->id);
            $damagedTypeIds = $assetDamages->pluck('damage_type_id')->unique();

            $damagedCritIds = $damagedTypeIds->intersect($criticalIds);
            $hasCritical = $damagedCritIds->isNotEmpty();

            $critDmgWeight = (float) $damageTypes->whereIn('id', $damagedCritIds->all())->sum(fn ($t) => (float) $t->default_cost);
            $nonCritDmgWeight = (float) $damageTypes
                ->whereIn('id', $damagedTypeIds->diff($criticalIds)->all())
                ->sum(fn ($t) => (float) $t->default_cost);

            // Availability band follows the "critical component" rule; the exact %
            // within the band is scaled by the cost of what is damaged.
            if ($damagedTypeIds->isEmpty()) {
                $availability = 100;
                $estado = 'assignable';
            } elseif ($hasCritical) {
                // A damaged critical component makes the unit unusable -> Critical band.
                $availability = (int) round(30 - 18 * ($critDmgWeight / $critTotal));
                $availability = max(5, min(35, $availability));
                $estado = 'critical';
            } else {
                // Only non-critical damage -> usable but "with damage".
                $availability = (int) round(84 - 30 * ($nonCritDmgWeight / $nonCritTotal));
                $availability = max(50, min(84, $availability));
                $estado = 'with_damage';
            }

            // Current asset photo + every photo uploaded on its damages.
            $assetPhoto = $asset->present()->imageSrc() ?: null;
            $damagePhotos = $assetDamages->flatMap(fn ($d) => $d->images)
                ->map(fn ($img) => $img->url)
                ->values()->all();

            return [
                'asset' => $asset,
                'availability' => $availability,
                'estado' => $estado,
                'damaged_type_ids' => $damagedTypeIds->all(),
                'critical_type_ids' => $damagedCritIds->all(),
                'repair_cost' => (float) $assetDamages->sum('cost'),
                'damage_count' => $assetDamages->count(),
                'asset_photo' => $assetPhoto,
                'damage_photos' => $damagePhotos,
                'last_note' => $lastNote?->note,
                'last_note_at' => $lastNote?->created_at,
            ];
        });

        $stats = [
            'total' => $rows->count(),
            'assignable' => $rows->where('estado', 'assignable')->count(),
            'with_damage' => $rows->where('estado', 'with_damage')->count(),
            'critical' => $rows->where('estado', 'critical')->count(),
            'avg_availability' => $rows->count() ? (int) round($rows->avg('availability')) : 0,
        ];

        return view('assets/availability')
            ->with('rows', $rows)
            ->with('damageTypes', $damageTypes)
            ->with('stats', $stats);
    }
}
