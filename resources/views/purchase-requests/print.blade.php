<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('admin/damages/general.quotation') }} {{ $pr->reference }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Helvetica Neue", Arial, sans-serif; color: #222; margin: 0; padding: 32px; font-size: 13px; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2c333e; padding-bottom: 14px; margin-bottom: 20px; }
        .head h1 { margin: 0; font-size: 22px; color: #2c333e; }
        .head .ref { font-size: 15px; font-weight: 700; }
        .meta { margin-bottom: 18px; }
        .meta div { margin-bottom: 3px; }
        .meta strong { display: inline-block; min-width: 130px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d0d5dd; padding: 8px 10px; text-align: left; }
        th { background: #2c333e; color: #fff; font-size: 12px; }
        td.num, th.num { text-align: right; }
        td.center, th.center { text-align: center; }
        tfoot td { font-weight: 700; background: #f4f6f9; }
        .notes { margin-top: 20px; padding: 10px 12px; background: #f7f9fb; border: 1px solid #e2e7ee; border-radius: 6px; }
        .print-btn { margin-bottom: 18px; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">🖨 {{ trans('admin/damages/general.generate_quotation') }}</button>
    </div>

    <div class="head">
        <div>
            <h1>{{ $snipeSettings->site_name ?? config('app.name') }}</h1>
            <div>{{ trans('admin/damages/general.quotation') }}</div>
        </div>
        <div style="text-align:right;">
            <div class="ref">{{ $pr->reference }}</div>
            <div>{{ $pr->created_at?->format('Y-m-d') }}</div>
            <div>{{ trans('admin/damages/general.pr_status_'.$pr->status) }}</div>
        </div>
    </div>

    <div class="meta">
        <div><strong>{{ trans('general.supplier') }}:</strong> {{ $pr->supplier?->name ?? '—' }}</div>
        <div><strong>{{ trans('admin/damages/general.pr_created_by') }}:</strong> {{ $pr->createdBy?->display_name ?? '—' }}</div>
        <div><strong>{{ trans('admin/damages/general.pr_items') }}:</strong> {{ $pr->components_count }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('admin/hardware/table.asset_tag') }}</th>
                <th>{{ trans('admin/hardware/form.model') }}</th>
                <th>{{ trans('admin/damages/table.damage_type') }}</th>
                <th class="center">{{ trans('admin/damages/table.quantity') }}</th>
                <th class="num">{{ trans('admin/damages/table.cost') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pr->damages as $i => $damage)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $damage->asset?->asset_tag ?? '—' }}</td>
                    <td>{{ $damage->asset?->model?->name ?? '—' }}</td>
                    <td>{{ $damage->damageType?->name ?? '—' }}</td>
                    <td class="center">{{ $damage->quantity }}</td>
                    <td class="num">{{ \App\Helpers\Helper::formatCurrencyOutput($damage->cost) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="num">{{ trans('admin/damages/table.total_cost') }}</td>
                <td class="center">{{ $pr->components_count }}</td>
                <td class="num">{{ \App\Helpers\Helper::formatCurrencyOutput($pr->total_cost) }}</td>
            </tr>
        </tfoot>
    </table>

    @if ($pr->notes)
        <div class="notes">
            <strong>{{ trans('general.notes') }}:</strong><br>
            {{ $pr->notes }}
        </div>
    @endif
</body>
</html>
