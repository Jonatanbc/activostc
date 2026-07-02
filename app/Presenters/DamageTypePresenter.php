<?php

namespace App\Presenters;

class DamageTypePresenter extends Presenter
{
    public static function dataTableLayout(): string
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/damages/table.damage_type'),
                'visible' => true,
            ], [
                'field' => 'default_cost',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/general.default_cost'),
                'visible' => true,
            ], [
                'field' => 'category.name',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.category'),
                'visible' => true,
            ], [
                'field' => 'damages_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/general.damages'),
                'visible' => true,
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'damage-typesActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }
}
