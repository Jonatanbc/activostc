<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\DamageType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class DamageTypesTransformer
{
    public function transformDamageTypes(Collection $types, $total)
    {
        $array = [];
        foreach ($types as $type) {
            $array[] = self::transformDamageType($type);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformDamageType(DamageType $type)
    {
        $array = [
            'id' => (int) $type->id,
            'name' => e($type->name),
            'default_cost' => Helper::formatCurrencyOutput($type->default_cost),
            'category' => ($type->category) ? [
                'id' => (int) $type->category->id,
                'name' => e($type->category->name),
            ] : null,
            'damages_count' => (int) $type->damages()->count(),
            'notes' => ($type->notes) ? e($type->notes) : null,
            'created_at' => Helper::getFormattedDateObject($type->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($type->updated_at, 'datetime'),
        ];

        $array['available_actions'] = [
            'update' => Gate::allows('update', DamageType::class),
            'delete' => $type->isDeletable(),
        ];

        return $array;
    }
}
