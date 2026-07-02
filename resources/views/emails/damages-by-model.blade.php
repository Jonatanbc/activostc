<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('admin/damages/general.damages_by_model_matrix') }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:'Helvetica Neue',Arial,sans-serif; color:#222;">
    <div style="max-width:760px; margin:0 auto; padding:24px;">
        <div style="background:#2c333e; color:#fff; padding:18px 22px; border-radius:12px 12px 0 0;">
            <h1 style="margin:0; font-size:20px;">{{ $snipeSettings->site_name ?? config('app.name') }}</h1>
            <div style="opacity:.85; font-size:14px;">{{ trans('admin/damages/general.damages_by_model_matrix') }}</div>
        </div>

        <div style="background:#fff; padding:20px 22px; border:1px solid #e2e7ee; border-top:none; border-radius:0 0 12px 12px;">
            <p style="margin:0 0 6px; font-size:13px; color:#666;">
                {{ trans('admin/damages/general.by_model_matrix_help') }}<br>
                <strong>{{ $onlyPending ? trans('admin/damages/general.only_pending') : trans('admin/damages/general.show_all') }}</strong>
                · {{ $generatedAt->format('Y-m-d H:i') }}
            </p>

            @if (count($rows))
                <div style="overflow-x:auto;">
                    <table cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse; margin-top:10px; font-size:13px;">
                        <thead>
                            <tr>
                                <th style="background:#2c333e; color:#fff; text-align:left; padding:8px 10px; border:1px solid #2c333e;">{{ trans('admin/damages/general.asset_type') }}</th>
                                <th style="background:#2c333e; color:#fff; text-align:left; padding:8px 10px; border:1px solid #2c333e;">{{ trans('admin/hardware/form.model') }}</th>
                                @foreach ($types as $type)
                                    <th style="background:#2c333e; color:#fff; text-align:center; padding:8px 10px; border:1px solid #2c333e;">{{ $type }}</th>
                                @endforeach
                                <th style="background:#2c333e; color:#fff; text-align:center; padding:8px 10px; border:1px solid #2c333e;">{{ trans('admin/damages/general.total') }}</th>
                                <th style="background:#2c333e; color:#fff; text-align:right; padding:8px 10px; border:1px solid #2c333e;">{{ trans('admin/damages/table.total_cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td style="padding:7px 10px; border:1px solid #e2e7ee;">{{ $row['category'] ?? '—' }}</td>
                                    <td style="padding:7px 10px; border:1px solid #e2e7ee;"><strong>{{ $row['model'] }}</strong></td>
                                    @foreach ($types as $type)
                                        <td style="padding:7px 10px; border:1px solid #e2e7ee; text-align:center;">{{ $row['cells'][$type] > 0 ? number_format($row['cells'][$type]) : '·' }}</td>
                                    @endforeach
                                    <td style="padding:7px 10px; border:1px solid #e2e7ee; text-align:center;"><strong>{{ number_format($row['total_qty']) }}</strong></td>
                                    <td style="padding:7px 10px; border:1px solid #e2e7ee; text-align:right;">{{ \App\Helpers\Helper::formatCurrencyOutput($row['total_cost']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="padding:8px 10px; border:1px solid #e2e7ee; background:#f4f6f9;"><strong>{{ trans('admin/damages/general.total') }}</strong></td>
                                <td style="padding:8px 10px; border:1px solid #e2e7ee; background:#f4f6f9;"></td>
                                @foreach ($types as $type)
                                    <td style="padding:8px 10px; border:1px solid #e2e7ee; background:#f4f6f9; text-align:center;"><strong>{{ number_format($colTotals[$type]) }}</strong></td>
                                @endforeach
                                <td style="padding:8px 10px; border:1px solid #e2e7ee; background:#f4f6f9; text-align:center;"><strong>{{ number_format($grandQty) }}</strong></td>
                                <td style="padding:8px 10px; border:1px solid #e2e7ee; background:#f4f6f9; text-align:right;"><strong>{{ \App\Helpers\Helper::formatCurrencyOutput($grandCost) }}</strong></td>
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
