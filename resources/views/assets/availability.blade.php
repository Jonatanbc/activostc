@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.availability_module') }}
    @parent
@stop

@section('content')
@php $typeNames = $damageTypes->pluck('name', 'id'); @endphp

<div class="row avail-cards">
    <div class="col-xs-6 col-sm-3">
        <div class="avail-card">
            <span class="avail-card-num">{{ $stats['total'] }}</span>
            <span class="avail-card-label"><i class="fa-solid fa-laptop text-muted"></i> {{ trans('admin/damages/general.availability_module') }}</span>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="avail-card">
            <span class="avail-card-num text-green">{{ $stats['assignable'] }}</span>
            <span class="avail-card-label"><i class="fa-solid fa-circle-check text-green"></i> {{ trans('admin/damages/general.card_assignable') }}</span>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="avail-card">
            <span class="avail-card-num" style="color:#e08e0b;">{{ $stats['with_damage'] }}</span>
            <span class="avail-card-label"><i class="fa-solid fa-triangle-exclamation" style="color:#e08e0b;"></i> {{ trans('admin/damages/general.card_with_damage') }}</span>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="avail-card">
            <span class="avail-card-num text-red">{{ $stats['critical'] }}</span>
            <span class="avail-card-label"><i class="fa-solid fa-circle-xmark text-red"></i> {{ trans('admin/damages/general.card_critical') }}</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/damages/general.availability_module') }}</h2>
                <div class="pull-right">
                    <input type="search" id="avail-search" class="form-control input-sm" style="width:220px;"
                           placeholder="{{ trans('general.search') }}…">
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted" style="margin-top:-6px;">{{ trans('admin/damages/general.availability_help') }}</p>

                <div class="table-responsive">
                    <table class="table table-striped avail-table">
                        <thead>
                            <tr>
                                <th>{{ trans('admin/hardware/table.asset_tag') }}</th>
                                <th>{{ trans('general.asset_model') }}</th>
                                <th>{{ trans('admin/hardware/form.serial') }}</th>
                                <th style="min-width:230px;">{{ trans('admin/damages/general.component_status') }}</th>
                                <th style="min-width:160px;">{{ trans('admin/damages/general.availability') }}</th>
                                <th>{{ trans('admin/damages/table.status') }}</th>
                                <th>{{ trans('general.location') }}</th>
                                <th style="min-width:130px;">{{ trans('admin/damages/general.photos_col') }}</th>
                                <th class="text-right">{{ trans('admin/damages/general.repair_cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                @php
                                    $asset = $row['asset'];
                                    $pct = $row['availability'];
                                    $barClass = $row['estado'] === 'assignable' ? 'progress-bar-success' : ($row['estado'] === 'with_damage' ? 'progress-bar-warning' : 'progress-bar-danger');
                                    $labelClass = $row['estado'] === 'assignable' ? 'label-success' : ($row['estado'] === 'with_damage' ? 'label-warning' : 'label-danger');
                                @endphp
                                <tr class="avail-row" data-search="{{ strtolower(($asset->asset_tag ?? '').' '.optional($asset->model)->name.' '.$asset->serial.' '.$asset->name) }}">
                                    <td>
                                        <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->asset_tag ?: '—' }}</a>
                                    </td>
                                    <td>{{ optional($asset->model)->name ?: '—' }}</td>
                                    <td><span class="text-muted">{{ $asset->serial ?: '—' }}</span></td>
                                    <td>
                                        @if (empty($row['damaged_type_ids']))
                                            <span class="label label-success"><i class="fa-solid fa-check"></i> {{ trans('admin/damages/general.component_ok') }}</span>
                                        @else
                                            @foreach ($row['damaged_type_ids'] as $tid)
                                                @php $isCrit = in_array($tid, $row['critical_type_ids']); @endphp
                                                <span class="comp-chip {{ $isCrit ? 'is-critical' : '' }}" title="{{ $isCrit ? trans('admin/damages/general.critical_component') : '' }}">
                                                    <i class="fa-solid {{ $isCrit ? 'fa-triangle-exclamation' : 'fa-wrench' }}"></i> {{ $typeNames[$tid] ?? '#'.$tid }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <div class="avail-bar-wrap">
                                            <div class="progress">
                                                <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $pct }}%;"></div>
                                            </div>
                                            <span class="avail-pct">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td><span class="label {{ $labelClass }}">{{ trans('admin/damages/general.estado_'.$row['estado']) }}</span></td>
                                    <td>{{ optional($asset->location)->name ?: '—' }}</td>
                                    <td>
                                        <div class="avail-photos">
                                            @if ($row['asset_photo'])
                                                <a href="{{ $row['asset_photo'] }}" target="_blank" rel="noopener" class="avail-photo pc" title="{{ trans('general.image') }}">
                                                    <img src="{{ $row['asset_photo'] }}" alt="">
                                                    <span class="avail-photo-tag"><i class="fa-solid fa-laptop"></i></span>
                                                </a>
                                            @endif
                                            @foreach ($row['damage_photos'] as $purl)
                                                <a href="{{ $purl }}" target="_blank" rel="noopener" class="avail-photo dmg" title="{{ trans('admin/damages/general.photos') }}">
                                                    <img src="{{ $purl }}" alt="">
                                                    <span class="avail-photo-tag"><i class="fa-solid fa-wrench"></i></span>
                                                </a>
                                            @endforeach
                                            @if (! $row['asset_photo'] && empty($row['damage_photos']))
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        {{ $row['repair_cost'] > 0 ? \App\Helpers\Helper::formatCurrencyOutput($row['repair_cost']) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted" style="padding:24px;">{{ trans('admin/damages/general.no_available_assets') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
.avail-cards { margin-bottom: 4px; }
.avail-card {
    background: #fff; border: 1px solid #e6eaef; border-radius: 12px;
    padding: 14px 16px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 2px;
    box-shadow: 0 2px 8px rgba(17,24,39,.05);
}
.avail-card-num { font-size: 26px; font-weight: 700; line-height: 1.1; color: #2d3748; }
.avail-card-label { font-size: 12.5px; color: #8793a3; font-weight: 600; }
.avail-table > thead > tr > th { font-size: 11px; text-transform: uppercase; letter-spacing: .3px; color: #9aa5b1; border-bottom: 2px solid #e6eaef; }
.avail-table > tbody > tr > td { vertical-align: middle; }
.comp-chip {
    display: inline-block; font-size: 11.5px; font-weight: 600; color: #b23b3b;
    background: #fdecec; border: 1px solid #f5cccc; border-radius: 14px;
    padding: 2px 9px; margin: 2px 3px 2px 0;
}
.comp-chip i { font-size: 9px; margin-right: 3px; opacity: .8; }
.comp-chip.is-critical { color: #fff; background: #c9302c; border-color: #ac2925; }
[data-theme="dark"] .comp-chip.is-critical { background: #c9302c; border-color: #ac2925; color: #fff; }
.avail-bar-wrap { display: flex; align-items: center; gap: 9px; }
.avail-bar-wrap .progress { flex: 1 1 auto; height: 12px; margin: 0; border-radius: 7px; background: #eef1f5; box-shadow: none; }
.avail-bar-wrap .progress-bar { border-radius: 7px; transition: width .4s ease; }
.avail-pct { flex: 0 0 auto; font-weight: 700; font-size: 12.5px; color: #4a5568; min-width: 34px; text-align: right; }
.avail-photos { display: flex; flex-wrap: wrap; gap: 5px; }
.avail-photo { position: relative; display: block; width: 38px; height: 38px; border-radius: 7px; overflow: hidden; border: 1px solid #e0e5ec; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.avail-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.avail-photo-tag { position: absolute; bottom: 0; right: 0; background: rgba(17,24,39,.72); color: #fff; font-size: 8px; padding: 1px 4px 1px 3px; border-top-left-radius: 5px; line-height: 1.4; }
.avail-photo.dmg .avail-photo-tag { background: rgba(201,48,44,.85); }
.avail-photo.pc { border-color: #b8c6d6; }
[data-theme="dark"] .avail-card { background: #2b303a; border-color: #3d4756; }
[data-theme="dark"] .avail-card-num { color: #e5e9ef; }
[data-theme="dark"] .avail-bar-wrap .progress { background: #333a45; }
[data-theme="dark"] .comp-chip { background: #3a2b2b; border-color: #5a3a3a; color: #f0a0a0; }
</style>
@endpush

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    (function () {
        var box = document.getElementById('avail-search');
        if (!box) return;
        box.addEventListener('input', function () {
            var q = box.value.trim().toLowerCase();
            document.querySelectorAll('.avail-row').forEach(function (row) {
                row.style.display = (!q || row.getAttribute('data-search').indexOf(q) > -1) ? '' : 'none';
            });
        });
    })();
</script>
@stop
