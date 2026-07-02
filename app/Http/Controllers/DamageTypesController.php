<?php

namespace App\Http\Controllers;

use App\Models\DamageType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DamageTypesController extends Controller
{
    public function index(): View
    {
        $this->authorize('index', DamageType::class);

        return view('damage-types.index');
    }

    public function create(): View
    {
        $this->authorize('create', DamageType::class);

        return view('damage-types.edit')->with('item', new DamageType);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DamageType::class);

        $type = new DamageType;
        $type->name = $request->input('name');
        $type->default_cost = $request->input('default_cost');
        $type->category_id = $request->input('category_id') ?: null;
        $type->notes = $request->input('notes');
        $type->created_by = auth()->id();

        if ($type->save()) {
            return redirect()->route('damage-types.index')
                ->with('success', trans('admin/damages/message.type_create_success'));
        }

        return redirect()->back()->withInput()->withErrors($type->getErrors());
    }

    public function edit(DamageType $damageType): View
    {
        $this->authorize('update', $damageType);

        return view('damage-types.edit')->with('item', $damageType);
    }

    public function update(Request $request, DamageType $damageType): RedirectResponse
    {
        $this->authorize('update', $damageType);

        $damageType->name = $request->input('name');
        $damageType->default_cost = $request->input('default_cost');
        $damageType->category_id = $request->input('category_id') ?: null;
        $damageType->notes = $request->input('notes');

        if ($damageType->save()) {
            return redirect()->route('damage-types.index')
                ->with('success', trans('admin/damages/message.type_update_success'));
        }

        return redirect()->back()->withInput()->withErrors($damageType->getErrors());
    }

    public function destroy(DamageType $damageType): RedirectResponse
    {
        $this->authorize('delete', $damageType);

        if (! $damageType->isDeletable()) {
            return redirect()->route('damage-types.index')
                ->with('error', trans('admin/damages/message.type_assoc_damages'));
        }

        $damageType->delete();

        return redirect()->route('damage-types.index')
            ->with('success', trans('admin/damages/message.type_delete_success'));
    }
}
