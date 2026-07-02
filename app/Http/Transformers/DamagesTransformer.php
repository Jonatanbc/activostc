<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\AssetDamage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class DamagesTransformer
{
    public function transformDamages(Collection $damages, $total)
    {
        $array = [];
        foreach ($damages as $damage) {
            $array[] = self::transformDamage($damage);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformDamage(AssetDamage $damage)
    {
        $array = [
            'id' => (int) $damage->id,
            'asset' => ($damage->asset) ? [
                'id' => (int) $damage->asset->id,
                'name' => e($damage->asset->name),
                'asset_tag' => e($damage->asset->asset_tag),
            ] : null,
            'model' => (($damage->asset) && ($damage->asset->model)) ? [
                'id' => (int) $damage->asset->model->id,
                'name' => e($damage->asset->model->name),
            ] : null,
            'assigned_to' => (($damage->asset) && ($damage->asset->assignedTo)) ? e($damage->asset->assignedTo->display_name) : null,
            'location' => (($damage->asset) && ($damage->asset->location)) ? e($damage->asset->location->name) : null,
            'damage_type' => ($damage->damageType) ? [
                'id' => (int) $damage->damageType->id,
                'name' => e($damage->damageType->name),
            ] : null,
            'quantity' => (int) $damage->quantity,
            'erp_purchase_code' => $damage->erp_purchase_code ? e($damage->erp_purchase_code) : null,
            'cost' => Helper::formatCurrencyOutput($damage->cost),
            'default_cost' => ($damage->damageType) ? Helper::formatCurrencyOutput($damage->damageType->default_cost) : null,
            'status' => e($damage->status),
            'status_label' => trans('admin/damages/general.status_'.$damage->status),
            'supplier' => ($damage->supplier) ? [
                'id' => (int) $damage->supplier->id,
                'name' => e($damage->supplier->name),
            ] : null,
            'reported_by' => ($damage->reportedBy) ? [
                'id' => (int) $damage->reportedBy->id,
                'name' => e($damage->reportedBy->display_name),
            ] : null,
            'reported_at' => Helper::getFormattedDateObject($damage->reported_at, 'date'),
            'repaired_at' => Helper::getFormattedDateObject($damage->repaired_at, 'date'),
            'photos' => $damage->relationLoaded('images')
                ? $damage->images->map(fn ($image) => $image->url)->values()->all()
                : $damage->images()->get()->map(fn ($image) => $image->url)->values()->all(),
            'notes' => ($damage->notes) ? Helper::parseEscapedMarkedownInline($damage->notes) : null,
            'created_at' => Helper::getFormattedDateObject($damage->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($damage->updated_at, 'datetime'),
        ];

        $array['available_actions'] = [
            'update' => (Gate::allows('update', Asset::class) && $damage->asset && $damage->asset->deleted_at == ''),
            'delete' => Gate::allows('delete', Asset::class),
        ];

        return $array;
    }

    /**
     * Flat rows for the per-unit damages report.
     */
    public function transformDamagesReport(Collection $damages, $total)
    {
        $array = [];
        foreach ($damages as $damage) {
            $array[] = [
                'id' => (int) $damage->id,
                'asset_tag' => ($damage->asset) ? e($damage->asset->asset_tag) : null,
                'asset_name' => ($damage->asset) ? e($damage->asset->name) : null,
                'model' => (($damage->asset) && ($damage->asset->model)) ? e($damage->asset->model->name) : null,
                'damage_type' => ($damage->damageType) ? e($damage->damageType->name) : null,
                'quantity' => (int) $damage->quantity,
                'cost' => Helper::formatCurrencyOutput($damage->cost),
                'status' => trans('admin/damages/general.status_'.$damage->status),
                'reported_at' => Helper::getFormattedDateObject($damage->reported_at, 'date'),
                'supplier' => ($damage->supplier) ? e($damage->supplier->name) : null,
            ];
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }
}
