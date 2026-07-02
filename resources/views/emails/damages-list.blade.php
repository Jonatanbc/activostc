<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('admin/damages/general.all_damages') }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:'Helvetica Neue',Arial,sans-serif; color:#222;">
    <div style="max-width:900px; margin:0 auto; padding:24px;">
        <div style="background:#2c333e; color:#fff; padding:18px 22px; border-radius:12px 12px 0 0;">
            <h1 style="margin:0; font-size:20px;">{{ $snipeSettings->site_name ?? config('app.name') }}</h1>
            <div style="opacity:.85; font-size:14px;">{{ trans('admin/damages/general.all_damages') }}</div>
        </div>

        <div style="background:#fff; padding:20px 22px; border:1px solid #e2e7ee; border-top:none; border-radius:0 0 12px 12px;">
            <p style="margin:0 0 6px; font-size:13px; color:#666;">
                <strong>{{ $onlyPending ? trans('admin/damages/general.only_pending') : trans('admin/damages/general.show_all') }}</strong>
                · {{ $generatedAt->format('Y-m-d H:i') }} · {{ $damages->count() }} {{ trans('admin/damages/general.all_damages') }}
            </p>

            @if ($damages->count())
                <div style="overflow-x:auto;">
                    <table cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse; margin-top:10px; font-size:12px;">
                        <thead>
                            <tr>
                                @foreach ([
                                    trans('admin/hardware/table.asset_tag'),
                                    trans('admin/hardware/table.title'),
                                    trans('admin/hardware/table.checkoutto'),
                                    trans('general.location'),
                                    trans('admin/hardware/form.model'),
                                    trans('admin/damages/table.damage_type'),
                                ] as $h)
                                    <th style="background:#2c333e; color:#fff; text-align:left; padding:7px 9px; border:1px solid #2c333e;">{{ $h }}</th>
                                @endforeach
                                <th style="background:#2c333e; color:#fff; text-align:center; padding:7px 9px; border:1px solid #2c333e;">{{ trans('admin/damages/table.quantity') }}</th>
                                <th style="background:#2c333e; color:#fff; text-align:right; padding:7px 9px; border:1px solid #2c333e;">{{ trans('admin/damages/table.cost') }}</th>
                                <th style="background:#2c333e; color:#fff; text-align:left; padding:7px 9px; border:1px solid #2c333e;">{{ trans('admin/damages/table.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($damages as $d)
                                <tr>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ $d->asset?->asset_tag ?? '—' }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ $d->asset?->name ?? '—' }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ $d->asset?->assignedTo?->display_name ?? '—' }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ $d->asset?->location?->name ?? '—' }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ $d->asset?->model?->name ?? '—' }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ $d->damageType?->name ?? '—' }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee; text-align:center;">{{ number_format($d->quantity) }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee; text-align:right;">{{ \App\Helpers\Helper::formatCurrencyOutput($d->cost) }}</td>
                                    <td style="padding:6px 9px; border:1px solid #e2e7ee;">{{ trans('admin/damages/general.status_'.$d->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="padding:8px 9px; border:1px solid #e2e7ee; background:#f4f6f9; text-align:right;"><strong>{{ trans('admin/damages/table.total_cost') }}</strong></td>
                                <td style="padding:8px 9px; border:1px solid #e2e7ee; background:#f4f6f9; text-align:center;"><strong>{{ number_format($damages->sum('quantity')) }}</strong></td>
                                <td style="padding:8px 9px; border:1px solid #e2e7ee; background:#f4f6f9; text-align:right;"><strong>{{ \App\Helpers\Helper::formatCurrencyOutput($totalCost) }}</strong></td>
                                <td style="padding:8px 9px; border:1px solid #e2e7ee; background:#f4f6f9;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p style="text-align:center; color:#999; padding:24px 0;">—</p>
            @endif
        </div>
    </div>
</body>
</html>
