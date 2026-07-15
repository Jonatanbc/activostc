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
                                <th class="sortable" data-type="text">{{ trans('general.category') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="text">{{ trans('admin/hardware/table.asset_tag') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="text">{{ trans('general.asset_model') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="text">{{ trans('admin/hardware/form.serial') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="num" style="min-width:230px;">{{ trans('admin/damages/general.component_status') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="num" style="min-width:160px;">{{ trans('admin/damages/general.availability') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="text">{{ trans('admin/damages/table.status') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="text">{{ trans('general.location') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="num" style="min-width:130px;">{{ trans('admin/damages/general.photos_col') }}<span class="sort-ind"></span></th>
                                <th class="sortable text-right" data-type="num">{{ trans('admin/damages/general.repair_cost') }}<span class="sort-ind"></span></th>
                                <th class="sortable" data-type="text" style="min-width:180px;">{{ trans('general.last_note') }}<span class="sort-ind"></span></th>
                                <th></th>
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
                                @php $categoryName = optional(optional($asset->model)->category)->name; @endphp
                                <tr class="avail-row" data-search="{{ strtolower(($asset->asset_tag ?? '').' '.optional($asset->model)->name.' '.$asset->serial.' '.$asset->name.' '.$categoryName.' '.strip_tags($row['last_note'] ?? '')) }}">
                                    <td data-sort="{{ strtolower($categoryName ?? '') }}">
                                        @if ($categoryName)
                                            <span class="cat-chip">{{ $categoryName }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->asset_tag ?: '—' }}</a>
                                    </td>
                                    <td>{{ optional($asset->model)->name ?: '—' }}</td>
                                    <td><span class="text-muted">{{ $asset->serial ?: '—' }}</span></td>
                                    <td data-sort="{{ count($row['damaged_type_ids']) }}">
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
                                    <td data-sort="{{ $pct }}">
                                        <div class="avail-bar-wrap">
                                            <div class="progress">
                                                <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $pct }}%;"></div>
                                            </div>
                                            <span class="avail-pct">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td><span class="label {{ $labelClass }}">{{ trans('admin/damages/general.estado_'.$row['estado']) }}</span></td>
                                    <td>{{ optional($asset->location)->name ?: '—' }}</td>
                                    <td data-sort="{{ ($row['asset_photo'] ? 1 : 0) + count($row['damage_photos']) }}">
                                        @php $gallery = 'avail-'.$asset->id; @endphp
                                        <div class="avail-photos">
                                            @if ($row['asset_photo'])
                                                <a href="{{ $row['asset_photo'] }}" data-toggle="lightbox" data-gallery="{{ $gallery }}"
                                                   data-title="{{ trans('general.image') }} · {{ $asset->asset_tag }}"
                                                   class="avail-photo pc" title="{{ trans('general.image') }}">
                                                    <img src="{{ $row['asset_photo'] }}" alt="">
                                                    <span class="avail-photo-tag"><i class="fa-solid fa-laptop"></i></span>
                                                </a>
                                            @endif
                                            @foreach ($row['damage_photos'] as $purl)
                                                <a href="{{ $purl }}" data-toggle="lightbox" data-gallery="{{ $gallery }}"
                                                   data-title="{{ trans('admin/damages/general.photos') }} · {{ $asset->asset_tag }}"
                                                   class="avail-photo dmg" title="{{ trans('admin/damages/general.photos') }}">
                                                    <img src="{{ $purl }}" alt="">
                                                    <span class="avail-photo-tag"><i class="fa-solid fa-wrench"></i></span>
                                                </a>
                                            @endforeach
                                            @if (! $row['asset_photo'] && empty($row['damage_photos']))
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right" data-sort="{{ $row['repair_cost'] }}">
                                        {{ $row['repair_cost'] > 0 ? \App\Helpers\Helper::formatCurrencyOutput($row['repair_cost']) : '—' }}
                                    </td>
                                    <td data-sort="{{ strtolower(strip_tags($row['last_note'] ?? '')) }}">
                                        @if (!empty($row['last_note']))
                                            <span class="avail-note" title="{{ strip_tags($row['last_note']) }}">{{ \Illuminate\Support\Str::limit(strip_tags($row['last_note']), 90) }}</span>
                                            @if ($row['last_note_at'])
                                                <span class="avail-note-date">{{ $row['last_note_at']->format('Y-m-d') }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-primary btn-xs js-request"
                                                data-asset-id="{{ $asset->id }}"
                                                data-asset-name="{{ ($asset->asset_tag ? $asset->asset_tag.' · ' : '').(optional($asset->model)->name ?: $asset->name) }}">
                                            <i class="fa-solid fa-hand-point-up"></i> {{ trans('admin/damages/general.request_button') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted" style="padding:24px;">{{ trans('admin/damages/general.no_available_assets') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Request questionnaire modal --}}
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('assets.request.store') }}" class="modal-content">
            {{ csrf_field() }}
            <input type="hidden" name="asset_id" id="req_asset_id">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa-solid fa-hand-point-up"></i> {{ trans('admin/damages/general.request_title') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="margin-top:-4px;"><strong id="req_asset_name"></strong></p>

                <div class="form-group">
                    <label for="assignee_name">{{ trans('admin/damages/general.assignee_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="assignee_name" name="assignee_name" required>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="assignee_id_number">{{ trans('admin/damages/general.assignee_id_number') }}</label>
                        <input type="text" class="form-control" id="assignee_id_number" name="assignee_id_number">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="assignee_position">{{ trans('admin/damages/general.assignee_position') }}</label>
                        <input type="text" class="form-control" id="assignee_position" name="assignee_position">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="assignee_location">{{ trans('admin/damages/general.assignee_location') }}</label>
                        <input type="text" class="form-control" id="assignee_location" name="assignee_location">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="needed_at">{{ trans('admin/damages/general.needed_at') }}</label>
                        <input type="date" class="form-control" id="needed_at" name="needed_at">
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ trans('admin/damages/general.account_type') }}</label>
                    <div>
                        <label class="radio-inline"><input type="radio" name="account_type" value="nueva" checked> {{ trans('admin/damages/general.account_new') }}</label>
                        <label class="radio-inline"><input type="radio" name="account_type" value="reemplazo"> {{ trans('admin/damages/general.account_replacement') }}</label>
                    </div>
                </div>
                <div class="form-group" id="replaces_wrap" style="display:none;">
                    <label for="replaces_person">{{ trans('admin/damages/general.replaces_person') }}</label>
                    <input type="text" class="form-control" id="replaces_person" name="replaces_person">
                </div>
                <div class="form-group">
                    <label for="justification">{{ trans('admin/damages/general.justification') }}</label>
                    <textarea class="form-control" id="justification" name="justification" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link text-muted" data-dismiss="modal">{{ trans('general.cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> {{ trans('admin/damages/general.request_button') }}</button>
            </div>
        </form>
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
.avail-table th.sortable { cursor: pointer; user-select: none; white-space: nowrap; }
.avail-table th.sortable:hover { color: #5a6b7f; }
.avail-table th.sortable .sort-ind { margin-left: 5px; font-size: 9px; opacity: .3; }
.avail-table th.sortable .sort-ind::before { content: "\2195"; }
.avail-table th.sortable.asc .sort-ind { opacity: .9; }
.avail-table th.sortable.asc .sort-ind::before { content: "\2191"; }
.avail-table th.sortable.desc .sort-ind { opacity: .9; }
.avail-table th.sortable.desc .sort-ind::before { content: "\2193"; }
.cat-chip {
    display: inline-block; font-size: 11.5px; font-weight: 600; color: #4a5568;
    background: #eef1f5; border-radius: 12px; padding: 2px 10px;
}
[data-theme="dark"] .cat-chip { background: #333a45; color: #cfd6df; }
.avail-note { display: block; font-size: 12px; color: #4a5568; line-height: 1.3; max-width: 260px; }
.avail-note-date { display: block; font-size: 10.5px; color: #9aa5b1; margin-top: 2px; }
[data-theme="dark"] .avail-note { color: #cfd6df; }
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

    // Click-to-sort on each sortable column header (A–Z / Z–A, numeric where applicable).
    (function () {
        var table = document.querySelector('.avail-table');
        if (!table) return;
        var tbody = table.querySelector('tbody');
        var headers = Array.prototype.slice.call(table.querySelectorAll('thead th'));

        function cellValue(row, idx, type) {
            var cell = row.children[idx];
            if (!cell) return type === 'num' ? 0 : '';
            var raw = cell.hasAttribute('data-sort') ? cell.getAttribute('data-sort') : cell.textContent;
            raw = (raw || '').trim();
            return type === 'num' ? (parseFloat(raw) || 0) : raw.toLowerCase();
        }

        table.querySelectorAll('thead th.sortable').forEach(function (th) {
            th.addEventListener('click', function () {
                var idx = headers.indexOf(th);
                var type = th.getAttribute('data-type') || 'text';
                var asc = !th.classList.contains('asc');

                headers.forEach(function (h) { h.classList.remove('asc', 'desc'); });
                th.classList.add(asc ? 'asc' : 'desc');

                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.avail-row'));
                rows.sort(function (a, b) {
                    var va = cellValue(a, idx, type), vb = cellValue(b, idx, type);
                    if (va < vb) return asc ? -1 : 1;
                    if (va > vb) return asc ? 1 : -1;
                    return 0;
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
            });
        });
    })();

    // Request questionnaire modal
    (function () {
        var modal = document.getElementById('requestModal');
        if (!modal) return;

        document.querySelectorAll('.js-request').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('req_asset_id').value = btn.getAttribute('data-asset-id');
                document.getElementById('req_asset_name').textContent = btn.getAttribute('data-asset-name');
                $('#requestModal').modal('show');
            });
        });

        // Show "replaces whom?" only for a replacement account.
        function toggleReplaces() {
            var val = modal.querySelector('input[name="account_type"]:checked');
            document.getElementById('replaces_wrap').style.display = (val && val.value === 'reemplazo') ? '' : 'none';
        }
        modal.querySelectorAll('input[name="account_type"]').forEach(function (r) {
            r.addEventListener('change', toggleReplaces);
        });
        toggleReplaces();
    })();
</script>
@stop
