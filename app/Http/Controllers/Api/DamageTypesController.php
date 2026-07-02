<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\DamageTypesTransformer;
use App\Models\DamageType;
use Illuminate\Http\Request;

class DamageTypesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', DamageType::class);

        $types = DamageType::with('category');

        if ($request->filled('search')) {
            $types->where('name', 'LIKE', '%'.$request->input('search').'%');
        }

        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);
        $order = $request->input('order') === 'desc' ? 'desc' : 'asc';
        $sort = in_array($request->input('sort'), ['id', 'name', 'default_cost']) ? $request->input('sort') : 'name';

        $total = $types->count();
        $types = $types->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        return (new DamageTypesTransformer)->transformDamageTypes($types, $total);
    }

    public function show(DamageType $damageType)
    {
        $this->authorize('view', $damageType);

        return (new DamageTypesTransformer)->transformDamageType($damageType);
    }

    public function store(Request $request)
    {
        $this->authorize('create', DamageType::class);

        $type = new DamageType;
        $type->name = $request->input('name');
        $type->default_cost = $request->input('default_cost');
        $type->category_id = $request->input('category_id');
        $type->notes = $request->input('notes');
        $type->created_by = auth()->id();

        if ($type->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new DamageTypesTransformer)->transformDamageType($type), trans('admin/damages/message.type_create_success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $type->getErrors()));
    }

    public function update(Request $request, DamageType $damageType)
    {
        $this->authorize('update', $damageType);

        $damageType->name = $request->input('name');
        $damageType->default_cost = $request->input('default_cost');
        $damageType->category_id = $request->input('category_id');
        $damageType->notes = $request->input('notes');

        if ($damageType->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new DamageTypesTransformer)->transformDamageType($damageType), trans('admin/damages/message.type_update_success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $damageType->getErrors()));
    }

    public function destroy(DamageType $damageType)
    {
        $this->authorize('delete', $damageType);

        if (! $damageType->isDeletable()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/damages/message.type_assoc_damages')));
        }

        $damageType->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/damages/message.type_delete_success')));
    }

    /**
     * select2-friendly list (id, text, default_cost) for the damage form.
     */
    public function selectlist(Request $request)
    {
        $this->authorize('view', DamageType::class);

        $types = DamageType::orderBy('name');
        if ($request->filled('search')) {
            $types->where('name', 'LIKE', '%'.$request->input('search').'%');
        }

        $items = $types->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'text' => $t->name,
                'default_cost' => $t->default_cost,
            ];
        });

        return response()->json(['results' => $items, 'pagination' => ['more' => false]]);
    }
}
