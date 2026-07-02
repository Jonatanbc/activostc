<?php

namespace App\Presenters;

class DamagesPresenter extends Presenter
{
    /**
     * Column layout for the per-asset damages datatable (asset "Damages" tab).
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'damage_type.name',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/damages/table.damage_type'),
                'visible' => true,
            ], [
                'field' => 'quantity',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.quantity'),
                'visible' => true,
            ], [
                'field' => 'cost',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.cost'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
            ], [
                'field' => 'status_label',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.status'),
                'visible' => true,
            ], [
                'field' => 'erp_purchase_code',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/damages/general.erp_purchase_code'),
                'visible' => true,
            ], [
                'field' => 'reported_at',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.reported_at'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'reported_by.name',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/damages/table.reported_by'),
                'visible' => true,
            ], [
                'field' => 'supplier.name',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.supplier'),
                'visible' => true,
            ], [
                'field' => 'photos',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/damages/general.photos'),
                'visible' => true,
                'formatter' => 'damagePhotosFormatter',
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.notes'),
                'visible' => true,
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'damagesActionsFormatter',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Column layout for the standalone Damages module listing (with bulk selection).
     */
    public static function listLayout($withCheckbox = true)
    {
        $layout = [];

        if ($withCheckbox) {
            $layout[] = [
                'field' => 'checkbox',
                'checkbox' => true,
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
            ];
        }

        $layout = array_merge($layout, [
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'asset.asset_tag',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/hardware/table.asset_tag'),
                'visible' => true,
            ], [
                'field' => 'asset.name',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/hardware/table.title'),
                'visible' => true,
            ], [
                'field' => 'assigned_to',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/hardware/table.checkoutto'),
                'visible' => true,
            ], [
                'field' => 'location',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.location'),
                'visible' => true,
            ], [
                'field' => 'model.name',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/hardware/form.model'),
                'visible' => true,
            ], [
                'field' => 'damage_type.name',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/damages/table.damage_type'),
                'visible' => true,
            ], [
                'field' => 'quantity',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.quantity'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
            ], [
                'field' => 'cost',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.cost'),
                'visible' => true,
                'footerFormatter' => 'sumFormatter',
            ], [
                'field' => 'status_label',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.status'),
                'visible' => true,
            ], [
                'field' => 'erp_purchase_code',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/damages/general.erp_purchase_code'),
                'visible' => true,
            ], [
                'field' => 'reported_at',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/damages/table.reported_at'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'reported_by.name',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/damages/table.reported_by'),
                'visible' => true,
            ], [
                'field' => 'supplier.name',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.supplier'),
                'visible' => true,
            ], [
                'field' => 'photos',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/damages/general.photos'),
                'visible' => true,
                'formatter' => 'damagePhotosFormatter',
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'damagesActionsFormatter',
            ],
        ]);

        return json_encode($layout);
    }

    /**
     * Column layout for the flat per-unit damages report.
     */
    public static function reportLayout()
    {
        $layout = [
            ['field' => 'asset_tag', 'searchable' => true, 'sortable' => true, 'title' => trans('admin/hardware/table.asset_tag')],
            ['field' => 'asset_name', 'searchable' => true, 'sortable' => true, 'title' => trans('admin/hardware/table.title')],
            ['field' => 'model', 'searchable' => true, 'sortable' => true, 'title' => trans('admin/hardware/form.model')],
            ['field' => 'damage_type', 'searchable' => true, 'sortable' => true, 'title' => trans('admin/damages/table.damage_type')],
            ['field' => 'quantity', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.quantity'), 'footerFormatter' => 'sumFormatter'],
            ['field' => 'cost', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.cost'), 'footerFormatter' => 'sumFormatter'],
            ['field' => 'status', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.status')],
            ['field' => 'reported_at', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.reported_at')],
            ['field' => 'supplier', 'searchable' => true, 'sortable' => true, 'title' => trans('general.supplier')],
        ];

        return json_encode($layout);
    }

    /**
     * Column layout for the by-model aggregated report (spare parts needed per model).
     */
    public static function byModelLayout()
    {
        $layout = [
            ['field' => 'model', 'searchable' => true, 'sortable' => true, 'title' => trans('admin/hardware/form.model')],
            ['field' => 'damage_type', 'searchable' => true, 'sortable' => true, 'title' => trans('admin/damages/table.damage_type')],
            ['field' => 'parts_needed', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.parts_needed'), 'footerFormatter' => 'sumFormatter'],
            ['field' => 'affected_assets', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.affected_assets')],
            ['field' => 'total_cost', 'searchable' => false, 'sortable' => true, 'title' => trans('admin/damages/table.total_cost'), 'footerFormatter' => 'sumFormatter'],
        ];

        return json_encode($layout);
    }
}
