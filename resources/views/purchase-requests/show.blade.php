@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.purchase_request') }} {{ $pr->reference }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('purchase-requests.index') }}" class="btn btn-default pull-right">
        <x-icon type="angle-left" /> {{ trans('admin/damages/general.purchase_requests') }}
    </a>
@stop

@php
    $prBadge = [
        'open' => 'label-default', 'quoted' => 'label-warning', 'ordered' => 'label-info',
        'received' => 'label-success', 'cancelled' => 'label-danger',
    ];
@endphp

@section('content')
<div class="damages-module">
    <div class="row">

        {{-- Left: grouped damages --}}
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        {{ $pr->reference }}
                        <span class="label {{ $prBadge[$pr->status] ?? 'label-default' }}" style="margin-left:6px;">
                            {{ trans('admin/damages/general.pr_status_'.$pr->status) }}
                        </span>
                    </h2>
                    <div class="box-tools pull-right">
                        <a href="{{ route('purchase-requests.print', $pr) }}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-print"></i> {{ trans('admin/damages/general.generate_quotation') }}
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin/hardware/table.asset_tag') }}</th>
                                    <th>{{ trans('admin/hardware/form.model') }}</th>
                                    <th>{{ trans('admin/damages/table.damage_type') }}</th>
                                    <th class="text-center">{{ trans('admin/damages/table.quantity') }}</th>
                                    <th class="text-right">{{ trans('admin/damages/table.cost') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pr->damages as $damage)
                                    <tr>
                                        <td>
                                            @if ($damage->asset)
                                                <a href="{{ route('hardware.show', $damage->asset_id) }}">{{ $damage->asset->asset_tag }}</a>
                                            @else — @endif
                                        </td>
                                        <td>{{ $damage->asset?->model?->name ?? '—' }}</td>
                                        <td>{{ $damage->damageType?->name ?? '—' }}</td>
                                        <td class="text-center">{{ $damage->quantity }}</td>
                                        <td class="text-right">{{ \App\Helpers\Helper::formatCurrencyOutput($damage->cost) }}</td>
                                        <td class="text-right">
                                            @can('update', \App\Models\Asset::class)
                                                <form method="POST" action="{{ route('purchase-requests.items.remove', [$pr, $damage]) }}"
                                                      onsubmit="return confirm('{{ trans('admin/damages/general.confirm_remove_item') }}');" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-xs btn-danger" title="{{ trans('admin/damages/general.remove_from_request') }}">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">—</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">{{ trans('admin/damages/table.total_cost') }}</th>
                                    <th class="text-center">{{ $pr->components_count }}</th>
                                    <th class="text-right">{{ \App\Helpers\Helper::formatCurrencyOutput($pr->total_cost) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: request details / editable --}}
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/damages/general.pr_status') }}</h2>
                </div>
                <form method="POST" action="{{ route('purchase-requests.update', $pr) }}">
                    @csrf @method('PUT')
                    <div class="box-body">
                        <div class="form-group">
                            <label for="status">{{ trans('admin/damages/general.pr_status') }}</label>
                            <select name="status" id="status" class="form-control">
                                @foreach (\App\Models\PurchaseRequest::STATUSES as $st)
                                    <option value="{{ $st }}" {{ old('status', $pr->status) === $st ? 'selected' : '' }}>
                                        {{ trans('admin/damages/general.pr_status_'.$st) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="supplier_id">{{ trans('general.supplier') }}</label>
                            <select name="supplier_id" id="supplier_id" class="form-control select2">
                                <option value="">—</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $pr->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">{{ trans('general.notes') }}</label>
                            <textarea name="notes" id="notes" rows="4" class="form-control">{{ old('notes', $pr->notes) }}</textarea>
                        </div>
                        <p class="text-muted" style="font-size:12px;">
                            {{ trans('admin/damages/general.pr_created_by') }}: {{ $pr->createdBy?->display_name ?? '—' }}<br>
                            {{ trans('admin/damages/general.pr_created_at') }}: {{ $pr->created_at?->format('Y-m-d H:i') }}
                        </p>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                        @can('update', \App\Models\Asset::class)
                            <button type="submit" form="pr-delete-form" class="btn btn-danger pull-right"
                                    onclick="return confirm('{{ trans('admin/damages/general.confirm_delete_request') }}');">
                                <i class="fa-solid fa-trash"></i> {{ trans('general.delete') }}
                            </button>
                        @endcan
                    </div>
                </form>
                @can('update', \App\Models\Asset::class)
                    <form method="POST" action="{{ route('purchase-requests.destroy', $pr) }}" id="pr-delete-form">
                        @csrf @method('DELETE')
                    </form>
                @endcan
            </div>
        </div>

    </div>
</div>
@stop

@push('css')
<style>
/* White column-header text on a dark header background */
.damages-module .table > thead > tr > th {
    background-color: #2c333e !important;
    color: #fff !important;
    border-color: #2c333e !important;
}
</style>
@endpush
