@php
    $isModal = request()->boolean('modal');
    $isEdit = (bool) $item->id;
    $primaryId = $item->damage_type_id;
@endphp
@extends($isModal ? 'layouts.modal' : 'layouts/default')

@section('title')
    @if ($isEdit)
        {{ trans('admin/damages/general.update_damage') }}
    @else
        {{ trans('admin/damages/general.add_damage') }}
    @endif
    @parent
@stop

@if (! $isModal)
@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-default pull-right">
        <x-icon type="angle-left" /> {{ trans('general.back') }}
    </a>
@stop
@endif

@section('content')
<div class="row damage-form">
    <div class="{{ $isModal ? 'col-xs-12' : 'col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2' }}">
        @if ($isEdit)
            <form class="form-vertical" id="damage-form" method="post" action="{{ route('damages.update', $item->id) }}" enctype="multipart/form-data" autocomplete="off">
                {{ method_field('PUT') }}
        @else
            <form class="form-vertical" id="damage-form" method="post" action="{{ route('damages.store') }}" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="asset_id" value="{{ $asset->id }}">
        @endif
        {{ csrf_field() }}
        @if ($isModal)<input type="hidden" name="modal" value="1">@endif

        <div class="box box-default damage-card">

            {{-- Header with asset identity --}}
            <div class="damage-hero">
                <div class="damage-hero-icon" aria-hidden="true">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="damage-hero-text">
                    <span class="damage-hero-eyebrow">
                        @if ($isEdit) {{ trans('admin/damages/general.update_damage') }} @else {{ trans('admin/damages/general.add_damage') }} @endif
                    </span>
                    <h2 class="damage-hero-title">
                        {{ $asset->present()->name() }}
                        @if ($asset->asset_tag) <small>· {{ $asset->asset_tag }}</small> @endif
                    </h2>
                </div>
            </div>

            <div class="box-body">

                {{-- Damaged components: chips to pick, compact rows for the picked ones --}}
                <p class="damage-hint">{{ $isEdit ? trans('admin/damages/general.add_more_types_hint') : trans('admin/damages/general.select_types_hint') }}</p>

                <div class="dmg-chips {{ $errors->has('types') ? 'has-error' : '' }}" id="dmg-chips">
                    @foreach ($damage_types as $type)
                        @php $isPrimary = $isEdit && $type->id == $primaryId; @endphp
                        <button type="button" class="dmg-chip {{ $isPrimary ? 'active locked' : '' }}"
                                data-type-id="{{ $type->id }}" data-cost="{{ $type->default_cost }}">
                            <i class="fa-solid fa-plus dmg-chip-ico"></i>
                            <span>{{ $type->name }}</span>
                        </button>
                    @endforeach
                </div>
                <span class="alert-msg" id="types-error" style="display:none;">{{ trans('admin/damages/general.no_type_selected') }}</span>

                {{-- Selected components (rows appear as chips are toggled) --}}
                <div class="dmg-selected-wrap" id="dmg-selected-wrap">
                    <table class="dmg-table">
                        <thead>
                            <tr>
                                <th class="c-name">{{ trans('admin/damages/table.damage_type') }}</th>
                                <th class="c-qty">{{ trans('admin/damages/table.quantity') }}</th>
                                <th class="c-cost">{{ trans('admin/damages/table.cost') }}</th>
                                <th class="c-photo">{{ trans('admin/damages/general.photos') }}</th>
                                <th class="c-x"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($damage_types as $type)
                                @php
                                    $isPrimary = $isEdit && $type->id == $primaryId;
                                    $rowQty = $isPrimary ? ($item->quantity ?? 1) : 1;
                                    $rowCost = $isPrimary ? $item->cost : null;
                                    $hasExisting = $isPrimary && $item->images->count();
                                @endphp
                                <tr class="dmg-item {{ $isPrimary ? '' : 'is-hidden' }}" data-type-id="{{ $type->id }}">
                                    <td class="c-name">
                                        <input type="checkbox" name="types[]" value="{{ $type->id }}" class="dmg-check" hidden
                                               {{ $isPrimary ? 'checked disabled' : '' }}>
                                        <span class="dmg-item-name">{{ $type->name }}</span>
                                        @if ($isPrimary)<span class="dmg-editing">{{ trans('admin/damages/general.editing_type') }}</span>@endif
                                    </td>
                                    <td class="c-qty">
                                        <input type="number" min="1" class="form-control input-sm dmg-qty"
                                               name="quantity[{{ $type->id }}]" value="{{ $rowQty }}" {{ $isPrimary ? '' : 'disabled' }}>
                                    </td>
                                    <td class="c-cost">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-addon">{{ $snipeSettings->default_currency ?? '$' }}</span>
                                            <input type="text" class="form-control dmg-cost" name="cost[{{ $type->id }}]"
                                                   value="{{ $rowCost }}" placeholder="{{ trans('admin/damages/general.cost_hint') }}"
                                                   {{ $isPrimary ? '' : 'disabled' }}>
                                        </div>
                                    </td>
                                    <td class="c-photo">
                                        <button type="button" class="btn btn-xs btn-default dmg-photo-btn">
                                            <i class="fa-solid fa-camera"></i> <span class="dmg-count"></span>
                                        </button>
                                        <input type="file" class="dmg-file" name="photos[{{ $type->id }}][]" accept="image/*" multiple hidden {{ $isPrimary ? '' : 'disabled' }}>
                                    </td>
                                    <td class="c-x">
                                        @unless ($isPrimary)
                                            <button type="button" class="btn btn-xs btn-link text-danger dmg-remove" title="{{ trans('button.delete') }}"><i class="fa-solid fa-xmark"></i></button>
                                        @endunless
                                    </td>
                                </tr>
                                <tr class="dmg-thumbs-row {{ $hasExisting ? '' : 'is-hidden' }}" data-type-id="{{ $type->id }}">
                                    <td colspan="5">
                                        <div class="dmg-thumbs">
                                            @if ($hasExisting)
                                                @foreach ($item->images as $image)
                                                    <div class="photo-cell existing" data-id="{{ $image->id }}">
                                                        <a href="{{ $image->url }}" target="_blank" rel="noopener"><img src="{{ $image->url }}" alt=""></a>
                                                        <label class="photo-remove" title="{{ trans('admin/damages/general.delete_photo') }}">
                                                            <input type="checkbox" name="delete_photos[]" value="{{ $image->id }}" hidden>
                                                            <i class="fa-solid fa-trash"></i>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="dmg-preview"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Shared details --}}
                <div class="row dmg-shared">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="status"><i class="fa-solid fa-flag text-muted"></i> {{ trans('admin/damages/table.status') }}</label>
                            <div class="status-select-wrap">
                                <span class="status-dot" id="status_dot" aria-hidden="true"></span>
                                <select name="status" id="status" class="form-control">
                                    @foreach (\App\Models\AssetDamage::STATUSES as $st)
                                        <option value="{{ $st }}" {{ old('status', $item->status ?? 'reported') === $st ? 'selected' : '' }}>
                                            {{ trans('admin/damages/general.status_'.$st) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="supplier_id"><i class="fa-solid fa-store text-muted"></i> {{ trans('general.supplier') }}</label>
                            <select name="supplier_id" id="supplier_id" class="form-control select2" style="width:100%;">
                                <option value="">—</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="erp_purchase_code"><i class="fa-solid fa-hashtag text-muted"></i> {{ trans('admin/damages/general.erp_purchase_code') }}</label>
                            <input type="text" name="erp_purchase_code" id="erp_purchase_code" class="form-control"
                                   value="{{ old('erp_purchase_code', $item->erp_purchase_code) }}" maxlength="100" placeholder="—">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="reported_at"><i class="fa-solid fa-calendar-check text-muted"></i> {{ trans('admin/damages/table.reported_at') }}</label>
                            <input type="date" name="reported_at" id="reported_at" class="form-control"
                                   value="{{ old('reported_at', optional($item->reported_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="repaired_at"><i class="fa-solid fa-calendar-check text-muted"></i> {{ trans('admin/damages/general.repaired_at') }}</label>
                            <input type="date" name="repaired_at" id="repaired_at" class="form-control"
                                   value="{{ old('repaired_at', optional($item->repaired_at)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="notes"><i class="fa-solid fa-note-sticky text-muted"></i> {{ trans('general.notes') }}</label>
                            <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes', $item->notes) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                @if ($isModal)
                    <button type="button" class="btn btn-link text-muted" onclick="window.parent.postMessage('damage-cancel','*')">{{ trans('general.cancel') }}</button>
                @else
                    <a href="{{ URL::previous() }}" class="btn btn-link text-muted">{{ trans('general.cancel') }}</a>
                @endif
                <button type="submit" class="btn btn-primary pull-right">
                    <x-icon type="checkmark" /> {{ trans('general.save') }}
                </button>
            </div>
        </div>
        </form>
    </div>
</div>
@stop

@push('css')
<style>
.damage-form .damage-card { border: none; border-radius: 14px; box-shadow: 0 6px 20px rgba(17,24,39,.10); overflow: hidden; }

/* Hero */
.damage-form .damage-hero { display: flex; align-items: center; gap: 14px; padding: 14px 20px; background: linear-gradient(135deg,#2c333e,#3b4552); color: #fff; }
.damage-form .damage-hero-icon { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 10px; background: rgba(255,255,255,.14); display: flex; align-items: center; justify-content: center; font-size: 20px; }
.damage-form .damage-hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: .6px; opacity: .8; }
.damage-form .damage-hero-title { margin: 2px 0 0; font-size: 17px; font-weight: 700; color: #fff; }
.damage-form .damage-hero-title small { color: rgba(255,255,255,.7); font-size: 13px; }

.damage-form .box-body { padding: 14px 20px; }
.damage-form .damage-hint { color: #8793a3; font-size: 12.5px; margin: 0 0 10px; }

/* Fields */
.damage-form .form-group label { font-weight: 600; font-size: 12.5px; color: #4a5568; margin-bottom: 5px; }
.damage-form .form-group label i { margin-right: 4px; }
.damage-form .form-control { border-radius: 8px; border: 1px solid #d7dce3; box-shadow: none; }
.damage-form .form-control:focus { border-color: #5b6b80; box-shadow: 0 0 0 3px rgba(91,107,128,.18); }
.damage-form .select2-container { width: 100% !important; }
.damage-form .select2-container--default .select2-selection--single { height: 34px; border: 1px solid #d7dce3; border-radius: 8px; }
.damage-form .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 32px; }
.damage-form .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px; }

/* --- Chips --- */
.damage-form .dmg-chips { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 6px; padding: 2px; }
.damage-form .dmg-chip {
    display: inline-flex; align-items: center; gap: 6px;
    border: 1px solid #cfd6df; background: #fff; color: #48566a;
    border-radius: 20px; padding: 5px 13px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all .13s ease; line-height: 1.2;
}
.damage-form .dmg-chip:hover { border-color: #8793a3; }
.damage-form .dmg-chip .dmg-chip-ico { font-size: 10px; transition: transform .13s ease; opacity: .7; }
.damage-form .dmg-chip.active { background: #2c8c5a; border-color: #2c8c5a; color: #fff; }
.damage-form .dmg-chip.active .dmg-chip-ico { transform: rotate(45deg); opacity: 1; } /* + becomes x-ish */
.damage-form .dmg-chip.locked { background: #b8860b; border-color: #b8860b; cursor: default; }
.damage-form .dmg-chips.has-error { outline: 1px dashed #d9534f; border-radius: 10px; }

/* --- Compact selected table --- */
.damage-form .dmg-selected-wrap { margin: 4px 0 6px; }
.damage-form .dmg-selected-wrap.is-empty { display: none; }
.damage-form .dmg-table { width: 100%; border-collapse: collapse; }
.damage-form .dmg-table thead th {
    font-size: 10.5px; text-transform: uppercase; letter-spacing: .3px; color: #9aa5b1;
    font-weight: 700; padding: 4px 8px; border-bottom: 1px solid #e6eaef; text-align: left;
}
.damage-form .dmg-table .c-qty { width: 74px; }
.damage-form .dmg-table .c-cost { width: 160px; }
.damage-form .dmg-table .c-photo { width: 92px; }
.damage-form .dmg-table .c-x { width: 30px; }
.damage-form .dmg-item > td { padding: 5px 8px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
.damage-form .dmg-item.is-hidden, .damage-form .dmg-thumbs-row.is-hidden { display: none; }
.damage-form .dmg-item-name { font-weight: 600; color: #2d3748; }
.damage-form .dmg-editing { margin-left: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: #b8860b; background: #fdf3d7; border-radius: 12px; padding: 2px 7px; }
.damage-form .dmg-table .input-group-addon { background: #f3f5f8; border-color: #d7dce3; font-weight: 600; color: #6b7684; }
.damage-form .dmg-table .form-control { border-radius: 6px; }
.damage-form .dmg-table .input-group-sm .input-group-addon { border-radius: 6px 0 0 6px; }
.damage-form .dmg-table .input-group-sm .form-control { border-radius: 0 6px 6px 0; }
.damage-form .dmg-photo-btn .dmg-count { font-weight: 700; }
.damage-form .dmg-thumbs-row > td { padding: 0 8px 8px; }
.damage-form .dmg-thumbs { display: flex; flex-wrap: wrap; gap: 7px; }

/* Photo cells (thumbnails) */
.damage-form .photo-cell { position: relative; width: 56px; height: 56px; border-radius: 8px; overflow: hidden; background: #eef1f5; box-shadow: 0 1px 4px rgba(0,0,0,.12); }
.damage-form .photo-cell img { width: 100%; height: 100%; object-fit: cover; display: block; }
.damage-form .photo-cell .photo-remove { position: absolute; top: 3px; right: 3px; width: 18px; height: 18px; margin: 0; border-radius: 50%; background: rgba(17,24,39,.62); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 9px; }
.damage-form .photo-cell .photo-remove:hover { background: #d9534f; }
.damage-form .photo-cell.marked { outline: 2px solid #d9534f; }
.damage-form .photo-cell.marked img { opacity: .4; filter: grayscale(1); }
.damage-form .dmg-preview { display: contents; }

/* Status dot */
.damage-form .dmg-shared { margin-top: 6px; }
.damage-form .status-select-wrap { position: relative; }
.damage-form .status-select-wrap .status-dot { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 9px; height: 9px; border-radius: 50%; background: #9aa5b1; pointer-events: none; z-index: 2; }
.damage-form .status-select-wrap select { padding-left: 28px; }
.status-dot.st-reported { background: #3c8dbc; } .status-dot.st-quoted { background: #f0ad4e; }
.status-dot.st-purchase_request { background: #8e44ad; } .status-dot.st-repaired { background: #27ae60; } .status-dot.st-discarded { background: #d9534f; }

.damage-form .box-footer { background: #f8fafc; border-top: 1px solid rgba(0,0,0,.06); padding: 12px 20px; }
.damage-form .alert-msg { color: #d9534f; font-size: 12px; display: inline-block; margin: 2px 0 6px; }

/* Dark mode */
[data-theme="dark"] .damage-form .form-group label { color: #c7ced8; }
[data-theme="dark"] .damage-form .form-control { background: #2b303a; border-color: #3d4756; color: #e5e9ef; }
[data-theme="dark"] .damage-form .dmg-chip { background: #2b303a; border-color: #3d4756; color: #c7ced8; }
[data-theme="dark"] .damage-form .dmg-table thead th { color: #8b97a7; border-bottom-color: #3d4756; }
[data-theme="dark"] .damage-form .dmg-item > td { border-bottom-color: #333a45; }
[data-theme="dark"] .damage-form .dmg-item-name { color: #e5e9ef; }
[data-theme="dark"] .damage-form .dmg-table .input-group-addon { background: #333a45; border-color: #3d4756; color: #aeb7c2; }
[data-theme="dark"] .damage-form .box-footer { background: #2b303a; border-top-color: rgba(255,255,255,.08); }
[data-theme="dark"] .damage-form .photo-cell { background: #333a45; }
</style>
@endpush

@if ($isModal)
@push('css')
<style>
body.modal-embed { padding: 0; }
.modal-embed .damage-form .damage-card { box-shadow: none; border-radius: 0; }
.modal-embed .damage-form .damage-hero { padding: 9px 16px; }
.modal-embed .damage-form .box-body { padding: 12px 16px; }
.modal-embed .damage-form .form-group { margin-bottom: 8px; }
</style>
@endpush
@endif

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
(function () {
    var wrap = document.getElementById('dmg-selected-wrap');

    function rowFor(id) { return wrap.querySelector('.dmg-item[data-type-id="' + id + '"]'); }
    function thumbsFor(id) { return wrap.querySelector('.dmg-thumbs-row[data-type-id="' + id + '"]'); }

    function refreshEmpty() {
        var anyVisible = wrap.querySelectorAll('.dmg-item:not(.is-hidden)').length > 0;
        wrap.classList.toggle('is-empty', !anyVisible);
    }

    function suggestCost(row, chip) {
        var qtyEl = row.querySelector('.dmg-qty');
        var costEl = row.querySelector('.dmg-cost');
        var base = parseFloat(chip.getAttribute('data-cost'));
        var qty = parseInt(qtyEl && qtyEl.value ? qtyEl.value : '1', 10) || 1;
        if (!isNaN(base) && base > 0 && costEl.value.trim() === '') { costEl.value = base * qty; }
    }

    function selectType(chip, on) {
        var id = chip.getAttribute('data-type-id');
        var row = rowFor(id);
        var thumbs = thumbsFor(id);
        chip.classList.toggle('active', on);
        chip.querySelector('.dmg-chip-ico').className = 'fa-solid ' + (on ? 'fa-check' : 'fa-plus') + ' dmg-chip-ico';
        row.classList.toggle('is-hidden', !on);
        row.querySelector('.dmg-check').checked = on;
        row.querySelectorAll('.dmg-qty, .dmg-cost, .dmg-file').forEach(function (el) { el.disabled = !on; });
        // thumbs row shows if this row has any photos (existing or new)
        var hasPhotos = thumbs.querySelector('.photo-cell') !== null;
        thumbs.classList.toggle('is-hidden', !(on && hasPhotos));
        if (on) { suggestCost(row, chip); }
        refreshEmpty();
    }

    // Chip toggles
    document.querySelectorAll('#dmg-chips .dmg-chip').forEach(function (chip) {
        if (chip.classList.contains('locked')) { return; } // primary in edit: fixed
        chip.addEventListener('click', function () { selectType(chip, !chip.classList.contains('active')); });
    });

    // Row-level wiring
    wrap.querySelectorAll('.dmg-item').forEach(function (row) {
        var id = row.getAttribute('data-type-id');
        var chip = document.querySelector('#dmg-chips .dmg-chip[data-type-id="' + id + '"]');
        var qtyEl = row.querySelector('.dmg-qty');
        var removeBtn = row.querySelector('.dmg-remove');

        if (qtyEl) {
            qtyEl.addEventListener('input', function () {
                var costEl = row.querySelector('.dmg-cost');
                if (costEl && costEl.value.trim() === '') { suggestCost(row, chip); }
            });
        }
        if (removeBtn) { removeBtn.addEventListener('click', function () { selectType(chip, false); }); }

        initDropzone(row, thumbsFor(id));
    });

    // Existing-photo delete toggle
    wrap.querySelectorAll('.dmg-thumbs').forEach(function (grid) {
        grid.addEventListener('click', function (e) {
            var label = e.target.closest('.photo-remove');
            if (!label) return;
            var cell = label.closest('.photo-cell');
            var cb = label.querySelector('input[type=checkbox]');
            setTimeout(function () { cell.classList.toggle('marked', cb.checked); }, 0);
        });
    });

    // Submit guard
    var form = document.getElementById('damage-form');
    form.addEventListener('submit', function (e) {
        if (form.querySelectorAll('.dmg-check:checked').length === 0) {
            e.preventDefault();
            document.getElementById('types-error').style.display = 'inline-block';
            document.getElementById('dmg-chips').classList.add('has-error');
        }
    });

    // Status dot
    (function () {
        var select = document.getElementById('status');
        var dot = document.getElementById('status_dot');
        if (!select || !dot) return;
        function paint() { dot.className = 'status-dot st-' + select.value; }
        select.addEventListener('change', paint); paint();
    })();

    // Compact per-row photo picker with thumbnails
    function initDropzone(row, thumbsRow) {
        var input = row.querySelector('.dmg-file');
        var btn = row.querySelector('.dmg-photo-btn');
        var count = row.querySelector('.dmg-count');
        var preview = thumbsRow.querySelector('.dmg-preview');
        if (!input || !btn || !preview) return;
        var store = new DataTransfer();

        function updateThumbs() {
            var hasAny = store.files.length > 0 || thumbsRow.querySelector('.photo-cell.existing') !== null;
            var chipActive = row.querySelector('.dmg-check').checked;
            thumbsRow.classList.toggle('is-hidden', !(chipActive && hasAny));
            count.textContent = store.files.length ? store.files.length : '';
        }

        function render() {
            preview.innerHTML = '';
            Array.prototype.forEach.call(store.files, function (file, idx) {
                var url = URL.createObjectURL(file);
                var cell = document.createElement('div');
                cell.className = 'photo-cell';
                cell.innerHTML = '<img src="' + url + '" alt=""><span class="photo-remove" data-idx="' + idx + '"><i class="fa-solid fa-xmark"></i></span>';
                preview.appendChild(cell);
            });
            input.files = store.files;
            updateThumbs();
        }

        function addFiles(list) {
            Array.prototype.forEach.call(list, function (f) { if (f.type && f.type.indexOf('image/') === 0) { store.items.add(f); } });
            render();
        }

        btn.addEventListener('click', function () { if (!input.disabled) input.click(); });
        input.addEventListener('change', function () { addFiles(input.files); });

        preview.addEventListener('click', function (e) {
            var b = e.target.closest('.photo-remove');
            if (!b) return;
            var idx = parseInt(b.getAttribute('data-idx'), 10);
            var next = new DataTransfer();
            Array.prototype.forEach.call(store.files, function (f, i) { if (i !== idx) next.items.add(f); });
            store = next; render();
        });

        updateThumbs();
    }

    refreshEmpty();
})();
</script>
@stop
