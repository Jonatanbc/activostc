@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.purchase_requests') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('damages.list') }}" class="btn btn-default pull-right">
        <x-icon type="angle-left" /> {{ trans('admin/damages/general.all_damages') }}
    </a>
@stop

@php
    $prBadge = [
        'open' => 'label-default',
        'quoted' => 'label-warning',
        'ordered' => 'label-info',
        'received' => 'label-success',
        'cancelled' => 'label-danger',
    ];
@endphp

@section('content')
<div class="damages-module">

    {{-- Summary cards --}}
    <div class="row damage-stats">
        <div class="col-lg-4 col-sm-4">
            <div class="stat-card stat-d">
                <i class="stat-icon fa-solid fa-file-invoice-dollar" aria-hidden="true"></i>
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.purchase_requests') }}</div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="stat-card stat-a">
                <i class="stat-icon fa-regular fa-folder-open" aria-hidden="true"></i>
                <div class="stat-value">{{ number_format($stats['open']) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.pr_total_open') }}</div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-4">
            <div class="stat-card stat-c">
                <i class="stat-icon fa-solid fa-money-bills" aria-hidden="true"></i>
                <div class="stat-value">{{ \App\Helpers\Helper::formatCurrencyOutput($stats['total_cost'] ?? 0) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.card_total_cost') }}</div>
            </div>
        </div>
    </div>

    <x-container>
        <x-box>
            <p class="text-muted" style="padding: 0 4px 10px;">
                {{ trans('admin/damages/general.purchase_requests_help') }}
            </p>

            @if ($requests->count())
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('admin/damages/general.pr_reference') }}</th>
                                <th>{{ trans('admin/damages/general.pr_status') }}</th>
                                <th class="text-center">{{ trans('admin/damages/general.pr_items') }}</th>
                                <th class="text-right">{{ trans('admin/damages/table.cost') }}</th>
                                <th>{{ trans('general.supplier') }}</th>
                                <th>{{ trans('admin/damages/general.pr_created_at') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $pr)
                                <tr>
                                    <td>
                                        <a href="{{ route('purchase-requests.show', $pr) }}"><strong>{{ $pr->reference }}</strong></a>
                                    </td>
                                    <td>
                                        <span class="label {{ $prBadge[$pr->status] ?? 'label-default' }}">
                                            {{ trans('admin/damages/general.pr_status_'.$pr->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $pr->damages_count }}</td>
                                    <td class="text-right">{{ \App\Helpers\Helper::formatCurrencyOutput($pr->total_cost) }}</td>
                                    <td>{{ $pr->supplier?->name ?? '—' }}</td>
                                    <td>{{ $pr->created_at?->format('Y-m-d') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('purchase-requests.show', $pr) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    {{ $requests->links() }}
                </div>
            @else
                <p class="text-center text-muted" style="padding: 30px 0;">
                    {{ trans('admin/damages/general.no_requests') }}
                </p>
            @endif
        </x-box>
    </x-container>
</div>
@stop

@push('css')
<style>
.damages-module .damage-stats { margin-bottom: 8px; }
.damages-module .stat-card {
    position: relative; min-height: 104px; padding: 18px 18px 18px 20px;
    border-radius: 16px; color: #fff; overflow: hidden; margin-bottom: 16px;
    box-shadow: 0 6px 18px rgba(17, 24, 39, .12);
}
.damages-module .stat-card .stat-value { font-size: 26px; font-weight: 700; line-height: 1.1; text-shadow: 0 1px 2px rgba(0,0,0,.18); }
.damages-module .stat-card .stat-label { font-size: 13px; font-weight: 600; opacity: .95; margin-top: 2px; }
.damages-module .stat-card .stat-icon { position: absolute; top: 16px; right: 16px; font-size: 34px; opacity: .28; }
.damages-module .stat-a { background: linear-gradient(135deg, #2c333e 0%, #3b4552 100%); }
.damages-module .stat-c { background: linear-gradient(135deg, #454e5a 0%, #566475 100%); }
.damages-module .stat-d { background: linear-gradient(135deg, #7b4397 0%, #a45bc9 100%); }
[data-theme="dark"] .damages-module .stat-card { box-shadow: 0 6px 18px rgba(0, 0, 0, .45); }

/* White column-header text on a dark header background */
.damages-module .table > thead > tr > th {
    background-color: #2c333e !important;
    color: #fff !important;
    border-color: #2c333e !important;
}
</style>
@endpush
