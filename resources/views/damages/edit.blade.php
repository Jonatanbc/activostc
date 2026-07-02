@php $isModal = request()->boolean('modal'); @endphp
@extends($isModal ? 'layouts.modal' : 'layouts/default')

@section('title')
    @if ($item->id)
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
        @if ($item->id)
            <form class="form-vertical" method="post" action="{{ route('damages.update', $item->id) }}" enctype="multipart/form-data" autocomplete="off">
                {{ method_field('PUT') }}
        @else
            <form class="form-vertical" method="post" action="{{ route('damages.store') }}" enctype="multipart/form-data" autocomplete="off">
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
                        @if ($item->id) {{ trans('admin/damages/general.update_damage') }} @else {{ trans('admin/damages/general.add_damage') }} @endif
                    </span>
                    <h2 class="damage-hero-title">
                        {{ $asset->present()->name() }}
                        @if ($asset->asset_tag) <small>· {{ $asset->asset_tag }}</small> @endif
                    </h2>
                </div>
            </div>

            <div class="box-body">

                {{-- Section: damage detail --}}
                <h4 class="damage-section-title"><i class="fa-solid fa-clipboard-list"></i> {{ trans('admin/damages/general.damages') }}</h4>

                {{-- Damage type (carries the standard cost via data-cost) --}}
                <div class="form-group {{ $errors->has('damage_type_id') ? 'has-error' : '' }}">
                    <label for="damage_type_id"><i class="fa-solid fa-screwdriver-wrench text-muted"></i> {{ trans('admin/damages/table.damage_type') }}</label>
                    <select name="damage_type_id" id="damage_type_id" class="form-control" required>
                        <option value="">—</option>
                        @foreach ($damage_types as $type)
                            <option value="{{ $type->id }}" data-cost="{{ $type->default_cost }}"
                                {{ old('damage_type_id', $item->damage_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}@if ($type->default_cost) ({{ \App\Helpers\Helper::formatCurrencyOutput($type->default_cost) }})@endif
                            </option>
                        @endforeach
                    </select>
                    {!! $errors->first('damage_type_id', '<span class="alert-msg">:message</span>') !!}
                </div>

                <div class="row">
                    {{-- Quantity --}}
                    <div class="col-sm-4">
                        <div class="form-group {{ $errors->has('quantity') ? 'has-error' : '' }}">
                            <label for="quantity"><i class="fa-solid fa-hashtag text-muted"></i> {{ trans('admin/damages/table.quantity') }}</label>
                            <input type="number" min="1" name="quantity" id="quantity" class="form-control"
                                   value="{{ old('quantity', $item->quantity ?? 1) }}" required>
                            {!! $errors->first('quantity', '<span class="alert-msg">:message</span>') !!}
                        </div>
                    </div>

                    {{-- Cost (real / quoted) --}}
                    <div class="col-sm-8">
                        <div class="form-group {{ $errors->has('cost') ? 'has-error' : '' }}">
                            <label for="cost"><i class="fa-solid fa-money-bills text-muted"></i> {{ trans('admin/damages/table.cost') }}</label>
                            <div class="input-group">
                                <span class="input-group-addon">{{ $snipeSettings->default_currency ?? '$' }}</span>
                                <input type="text" name="cost" id="cost" class="form-control"
                                       value="{{ old('cost', $item->cost) }}" placeholder="{{ trans('admin/damages/general.cost_hint') }}">
                            </div>
                            {!! $errors->first('cost', '<span class="alert-msg">:message</span>') !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Status --}}
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

                    {{-- Supplier --}}
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

                    {{-- ERP purchase-request code --}}
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="erp_purchase_code"><i class="fa-solid fa-hashtag text-muted"></i> {{ trans('admin/damages/general.erp_purchase_code') }}</label>
                            <input type="text" name="erp_purchase_code" id="erp_purchase_code" class="form-control"
                                   value="{{ old('erp_purchase_code', $item->erp_purchase_code) }}" maxlength="100" placeholder="—">
                        </div>
                    </div>
                </div>

                {{-- Section: dates --}}
                <h4 class="damage-section-title"><i class="fa-regular fa-calendar"></i> {{ trans('admin/damages/table.reported_at') }} / {{ trans('admin/damages/general.repaired_at') }}</h4>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="reported_at"><i class="fa-solid fa-calendar-check text-muted"></i> {{ trans('admin/damages/table.reported_at') }}</label>
                            <input type="date" name="reported_at" id="reported_at" class="form-control"
                                   value="{{ old('reported_at', optional($item->reported_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="repaired_at"><i class="fa-solid fa-calendar-check text-muted"></i> {{ trans('admin/damages/general.repaired_at') }}</label>
                            <input type="date" name="repaired_at" id="repaired_at" class="form-control"
                                   value="{{ old('repaired_at', optional($item->repaired_at)->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label for="notes"><i class="fa-solid fa-note-sticky text-muted"></i> {{ trans('general.notes') }}</label>
                    <textarea name="notes" id="notes" rows="{{ $isModal ? 2 : 3 }}" class="form-control">{{ old('notes', $item->notes) }}</textarea>
                </div>

                {{-- Section: photos --}}
                <h4 class="damage-section-title"><i class="fa-solid fa-camera"></i> {{ trans('admin/damages/general.photos') }}</h4>

                {{-- Existing photos (edit only) --}}
                @if ($item->id && $item->images->count())
                    <div class="photo-grid" id="existing-photos">
                        @foreach ($item->images as $image)
                            <div class="photo-cell existing" data-id="{{ $image->id }}">
                                <a href="{{ $image->url }}" target="_blank" rel="noopener">
                                    <img src="{{ $image->url }}" alt="{{ trans('admin/damages/general.photos') }}">
                                </a>
                                <label class="photo-remove" title="{{ trans('admin/damages/general.delete_photo') }}">
                                    <input type="checkbox" name="delete_photos[]" value="{{ $image->id }}" hidden>
                                    <i class="fa-solid fa-trash"></i>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Dropzone for new photos --}}
                <div class="form-group {{ $errors->has('photos.0') || $errors->has('photos') ? 'has-error' : '' }}">
                    <div class="photo-dropzone" id="photo-dropzone" role="button" tabindex="0">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="dz-title">{{ trans('admin/damages/general.photos') }}</span>
                        <span class="dz-hint">{{ trans('admin/damages/general.photos_hint') }}</span>
                    </div>
                    <input type="file" name="photos[]" id="photos" accept="image/*" multiple class="sr-only">
                    <div class="photo-grid" id="photo-preview"></div>
                    {!! $errors->first('photos.*', '<span class="alert-msg">:message</span>') !!}
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
.damage-form .damage-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(17, 24, 39, .10);
    overflow: hidden;
}

/* Hero header */
.damage-form .damage-hero {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 22px;
    background: linear-gradient(135deg, #2c333e 0%, #3b4552 100%);
    color: #fff;
}
.damage-form .damage-hero-icon {
    flex: 0 0 auto;
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .14);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.damage-form .damage-hero-eyebrow {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .6px;
    opacity: .8;
}
.damage-form .damage-hero-title {
    margin: 2px 0 0;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
}
.damage-form .damage-hero-title small { color: rgba(255, 255, 255, .7); font-size: 14px; }

/* Section titles */
.damage-form .damage-section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #8793a3;
    margin: 22px 0 14px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(0, 0, 0, .07);
}
.damage-form .damage-section-title:first-child { margin-top: 4px; }
.damage-form .damage-section-title i { margin-right: 6px; }

/* Fields */
.damage-form .form-group label {
    font-weight: 600;
    font-size: 13px;
    color: #4a5568;
    margin-bottom: 6px;
}
.damage-form .form-group label i { margin-right: 4px; }
.damage-form .form-control {
    border-radius: 9px;
    border: 1px solid #d7dce3;
    box-shadow: none;
    height: 40px;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.damage-form textarea.form-control { height: auto; }
.damage-form .form-control:focus {
    border-color: #5b6b80;
    box-shadow: 0 0 0 3px rgba(91, 107, 128, .18);
}
.damage-form .select2-container { width: 100% !important; }
.damage-form .select2-container--default .select2-selection--single {
    height: 40px;
    border: 1px solid #d7dce3;
    border-radius: 9px;
}
.damage-form .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
.damage-form .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
.damage-form .input-group-addon {
    border-radius: 9px 0 0 9px;
    border-color: #d7dce3;
    background: #f3f5f8;
    font-weight: 600;
    color: #6b7684;
}
.damage-form .input-group .form-control { border-radius: 0 9px 9px 0; }

/* Status colored dot */
.damage-form .status-select-wrap { position: relative; }
.damage-form .status-select-wrap .status-dot {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #9aa5b1;
    pointer-events: none;
    z-index: 2;
}
.damage-form .status-select-wrap select { padding-left: 30px; }
.status-dot.st-reported         { background: #3c8dbc; }
.status-dot.st-quoted           { background: #f0ad4e; }
.status-dot.st-purchase_request { background: #8e44ad; }
.status-dot.st-repaired         { background: #27ae60; }
.status-dot.st-discarded        { background: #d9534f; }

/* Photo grid + cells */
.damage-form .photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}
.damage-form .photo-cell {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    background: #eef1f5;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .10);
}
.damage-form .photo-cell img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.damage-form .photo-cell .photo-remove {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    margin: 0;
    border-radius: 50%;
    background: rgba(17, 24, 39, .62);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: background .15s ease;
}
.damage-form .photo-cell .photo-remove:hover { background: #d9534f; }
/* Existing photo marked for deletion */
.damage-form .photo-cell.marked { outline: 3px solid #d9534f; }
.damage-form .photo-cell.marked img { opacity: .4; filter: grayscale(1); }
.damage-form .photo-cell.marked .photo-remove { background: #d9534f; }

/* Dropzone */
.damage-form .photo-dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 28px 16px;
    border: 2px dashed #c4cdd8;
    border-radius: 12px;
    background: #f8fafc;
    color: #7b8794;
    cursor: pointer;
    text-align: center;
    transition: border-color .15s ease, background .15s ease, color .15s ease;
}
.damage-form .photo-dropzone:hover,
.damage-form .photo-dropzone:focus,
.damage-form .photo-dropzone.dragover {
    border-color: #5b6b80;
    background: #eef2f7;
    color: #48566a;
    outline: none;
}
.damage-form .photo-dropzone i { font-size: 26px; }
.damage-form .photo-dropzone .dz-title { font-weight: 600; }
.damage-form .photo-dropzone .dz-hint { font-size: 12px; opacity: .85; }

/* Footer */
.damage-form .box-footer {
    background: #f8fafc;
    border-top: 1px solid rgba(0, 0, 0, .06);
    padding: 14px 22px;
}

.damage-form .alert-msg { color: #d9534f; font-size: 12px; display: inline-block; margin-top: 4px; }

/* Dark mode */
[data-theme="dark"] .damage-form .damage-card { box-shadow: 0 6px 20px rgba(0, 0, 0, .45); }
[data-theme="dark"] .damage-form .damage-section-title { color: #8b97a7; border-bottom-color: rgba(255, 255, 255, .08); }
[data-theme="dark"] .damage-form .form-group label { color: #c7ced8; }
[data-theme="dark"] .damage-form .form-control { background: #2b303a; border-color: #3d4756; color: #e5e9ef; }
[data-theme="dark"] .damage-form .input-group-addon { background: #333a45; border-color: #3d4756; color: #aeb7c2; }
[data-theme="dark"] .damage-form .photo-dropzone { background: #2b303a; border-color: #454f5e; color: #9aa5b1; }
[data-theme="dark"] .damage-form .photo-dropzone:hover,
[data-theme="dark"] .damage-form .photo-dropzone.dragover { background: #333a45; }
[data-theme="dark"] .damage-form .box-footer { background: #2b303a; border-top-color: rgba(255, 255, 255, .08); }
[data-theme="dark"] .damage-form .photo-cell { background: #333a45; }
</style>
@endpush

@if ($isModal)
@push('css')
<style>
/* Compact layout so the whole form fits inside the modal without scrolling */
body.modal-embed { padding: 0; }
.modal-embed .damage-form .damage-card { box-shadow: none; border-radius: 0; }
.modal-embed .damage-form .damage-hero { padding: 9px 16px; gap: 11px; }
.modal-embed .damage-form .damage-hero-icon { width: 36px; height: 36px; font-size: 17px; border-radius: 9px; }
.modal-embed .damage-form .damage-hero-eyebrow { font-size: 10px; }
.modal-embed .damage-form .damage-hero-title { font-size: 15px; }
.modal-embed .damage-form .box-body { padding: 12px 16px; }
.modal-embed .damage-form .damage-section-title { margin: 10px 0 7px; padding-bottom: 4px; font-size: 11px; }
.modal-embed .damage-form .damage-section-title:first-child { margin-top: 0; }
.modal-embed .damage-form .form-group { margin-bottom: 8px; }
.modal-embed .damage-form .form-group label { margin-bottom: 2px; font-size: 12px; }
.modal-embed .damage-form .form-control { height: 34px; border-radius: 7px; }
.modal-embed .damage-form textarea.form-control { height: auto; }
.modal-embed .damage-form .select2-container--default .select2-selection--single { height: 34px; border-radius: 7px; }
.modal-embed .damage-form .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 32px; }
.modal-embed .damage-form .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px; }
.modal-embed .damage-form .input-group-addon { border-radius: 7px 0 0 7px; }
.modal-embed .damage-form .input-group .form-control { border-radius: 0 7px 7px 0; }
/* Photos: tighter dropzone + smaller thumbnails */
.modal-embed .damage-form .photo-dropzone { padding: 12px; border-radius: 9px; }
.modal-embed .damage-form .photo-dropzone i { font-size: 20px; }
.modal-embed .damage-form .photo-dropzone .dz-hint { display: none; }
.modal-embed .damage-form .photo-grid { grid-template-columns: repeat(auto-fill, minmax(64px, 1fr)); gap: 8px; margin-bottom: 10px; }
.modal-embed .damage-form .box-footer { padding: 10px 16px; }
/* Two-column section rows already exist; keep help text tight */
.modal-embed .damage-form .help-block { margin: 3px 0 0; font-size: 11px; }
</style>
@endpush
@endif

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    // --- Autofill the cost from the selected damage type's standard cost x quantity (only when blank) ---
    function suggestCost() {
        var opt = document.querySelector('#damage_type_id option:checked');
        var cost = opt ? parseFloat(opt.getAttribute('data-cost')) : NaN;
        var qty = parseInt(document.getElementById('quantity').value || '1', 10);
        var costField = document.getElementById('cost');
        if (!isNaN(cost) && costField.value.trim() === '') {
            costField.value = cost * qty;
        }
    }
    document.getElementById('damage_type_id').addEventListener('change', function () {
        document.getElementById('cost').value = '';
        suggestCost();
    });
    document.getElementById('quantity').addEventListener('input', suggestCost);

    // --- Status colored dot ---
    (function () {
        var select = document.getElementById('status');
        var dot = document.getElementById('status_dot');
        function paint() {
            dot.className = 'status-dot st-' + select.value;
        }
        select.addEventListener('change', paint);
        paint();
    })();

    // --- Existing photos: toggle "marked for deletion" ---
    (function () {
        var existing = document.getElementById('existing-photos');
        if (!existing) return;
        existing.addEventListener('click', function (e) {
            var label = e.target.closest('.photo-remove');
            if (!label) return;
            var cell = label.closest('.photo-cell');
            var cb = label.querySelector('input[type=checkbox]');
            // Let the native checkbox toggle, then reflect state.
            setTimeout(function () { cell.classList.toggle('marked', cb.checked); }, 0);
        });
    })();

    // --- Modern dropzone with live preview for new photos ---
    (function () {
        var input = document.getElementById('photos');
        var dropzone = document.getElementById('photo-dropzone');
        var preview = document.getElementById('photo-preview');
        var store = new DataTransfer();

        function render() {
            preview.innerHTML = '';
            Array.prototype.forEach.call(store.files, function (file, idx) {
                var url = URL.createObjectURL(file);
                var cell = document.createElement('div');
                cell.className = 'photo-cell';
                cell.innerHTML =
                    '<img src="' + url + '" alt="">' +
                    '<span class="photo-remove" data-idx="' + idx + '" title="{{ trans('admin/damages/general.delete_photo') }}">' +
                    '<i class="fa-solid fa-xmark"></i></span>';
                preview.appendChild(cell);
            });
            input.files = store.files;
        }

        function addFiles(list) {
            Array.prototype.forEach.call(list, function (f) {
                if (f.type && f.type.indexOf('image/') === 0) { store.items.add(f); }
            });
            render();
        }

        dropzone.addEventListener('click', function () { input.click(); });
        dropzone.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
        });

        input.addEventListener('change', function () {
            // Merge dialog selection into our persistent store.
            var picked = input.files;
            addFiles(picked);
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.classList.remove('dragover'); });
        });
        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files) { addFiles(e.dataTransfer.files); }
        });

        preview.addEventListener('click', function (e) {
            var btn = e.target.closest('.photo-remove');
            if (!btn) return;
            var idx = parseInt(btn.getAttribute('data-idx'), 10);
            var next = new DataTransfer();
            Array.prototype.forEach.call(store.files, function (f, i) { if (i !== idx) next.items.add(f); });
            store = next;
            render();
        });
    })();
</script>
@stop
