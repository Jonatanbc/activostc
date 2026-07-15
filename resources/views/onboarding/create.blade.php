@extends('layouts/default')

@section('title')
    {{ trans('admin/onboarding/general.module_title') }}
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-default ob-box">
            <div class="box-header with-border">
                <h2 class="box-title"><i class="fa-solid fa-user-plus"></i> {{ trans('admin/onboarding/general.module_title') }}</h2>
            </div>
            <div class="box-body">
                <p class="text-muted">{{ trans('admin/onboarding/general.wizard_intro') }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('onboarding.store') }}" id="ob-form">
                    {{ csrf_field() }}
                    <input type="hidden" name="asset_id" id="asset_id_input" value="{{ old('asset_id') }}">


                    {{-- Step 1: cargo --}}
                    <div class="ob-step" data-step="1">
                        <label for="position" class="ob-label"><span class="ob-num">1</span> {{ trans('admin/onboarding/general.position') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="position" name="position" value="{{ old('position') }}"
                               placeholder="{{ trans('admin/onboarding/general.position_ph') }}" required autocomplete="off">
                    </div>

                    {{-- Step 2: nombre --}}
                    <div class="ob-step ob-hidden" data-step="2">
                        <label for="employee_name" class="ob-label"><span class="ob-num">2</span> {{ trans('admin/onboarding/general.employee_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="employee_name" name="employee_name" value="{{ old('employee_name') }}"
                               placeholder="{{ trans('admin/onboarding/general.employee_name_ph') }}" autocomplete="off">
                    </div>

                    {{-- Step 3: tipo de ingreso --}}
                    <div class="ob-step ob-hidden" data-step="3">
                        <label class="ob-label"><span class="ob-num">3</span> {{ trans('admin/onboarding/general.entry_type') }} <span class="text-danger">*</span></label>
                        <div class="ob-choice-group">
                            <label class="ob-choice">
                                <input type="radio" name="entry_type" value="nuevo" {{ old('entry_type') === 'nuevo' ? 'checked' : '' }}>
                                <span><i class="fa-solid fa-star"></i> {{ trans('admin/onboarding/general.entry_new') }}</span>
                            </label>
                            <label class="ob-choice">
                                <input type="radio" name="entry_type" value="reemplazo" {{ old('entry_type') === 'reemplazo' ? 'checked' : '' }}>
                                <span><i class="fa-solid fa-right-left"></i> {{ trans('admin/onboarding/general.entry_replacement') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Step 3-reemplazo: usuario reemplazado + equipo a reasignar --}}
                    <div class="ob-step ob-hidden" data-step="replace">
                        <label for="replace_user" class="ob-label">{{ trans('admin/onboarding/general.replaces_user') }} <span class="text-danger">*</span></label>
                        <p class="text-muted ob-help">{{ trans('admin/onboarding/general.replaces_help') }}</p>
                        <select id="replace_user" name="replaces_user_id" class="form-control ob-select" data-placeholder="—">
                            <option value=""></option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ (string) old('replaces_user_id') === (string) $u->id ? 'selected' : '' }}>
                                    {{ trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: $u->username }}{{ $u->employee_num ? ' ('.$u->employee_num.')' : '' }}
                                </option>
                            @endforeach
                        </select>

                        <div id="replace_device_wrap" style="margin-top:14px;">
                            <label for="replace_asset" class="ob-label-sm">{{ trans('admin/onboarding/general.assigned_device') }}</label>
                            <select id="replace_asset" class="form-control ob-select">
                                <option value=""></option>
                            </select>
                            <p class="text-muted ob-help" id="replace_device_msg" style="display:none;"></p>
                        </div>
                    </div>

                    {{-- Step 3-nuevo: equipo disponible a asignar --}}
                    <div class="ob-step ob-hidden" data-step="new">
                        <label for="new_asset" class="ob-label">{{ trans('admin/onboarding/general.choose_device') }}</label>
                        <p class="text-muted ob-help">{{ trans('admin/onboarding/general.choose_device_help') }}</p>
                        @if ($availableAssets->isEmpty())
                            <div class="alert alert-warning" style="margin-bottom:0;">{{ trans('admin/onboarding/general.no_available_assets') }}</div>
                        @else
                            <select id="new_asset" class="form-control ob-select"
                                    data-placeholder="{{ trans('admin/onboarding/general.choose_device_ph') }}">
                                <option value=""></option>
                                @foreach ($availableAssets as $a)
                                    <option value="{{ $a->id }}" {{ (string) old('asset_id') === (string) $a->id ? 'selected' : '' }}>
                                        {{ ($a->asset_tag ? $a->asset_tag.' · ' : '').(optional($a->model)->name ?: $a->name) }}{{ $a->serial ? ' · '.$a->serial : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Step 4: componentes / accesorios --}}
                    <div class="ob-step ob-hidden" data-step="4">
                        <label class="ob-label"><span class="ob-num">4</span> {{ trans('admin/onboarding/general.components') }}</label>
                        <p class="text-muted ob-help">{{ trans('admin/onboarding/general.components_help') }}</p>
                        @if ($accessories->isEmpty())
                            <p class="text-muted">{{ trans('admin/onboarding/general.no_components') }}</p>
                        @else
                            <div class="ob-grid">
                                @foreach ($accessories as $acc)
                                    @php $remaining = (int) ($acc->qty - $acc->checkouts_count); @endphp
                                    <label class="ob-check">
                                        <input type="checkbox" name="accessories[]" value="{{ $acc->id }}"
                                               {{ in_array((string) $acc->id, (array) old('accessories', []), true) ? 'checked' : '' }}>
                                        <span class="ob-check-name">{{ $acc->name }}</span>
                                        <span class="ob-badge">{{ $remaining }} {{ trans('admin/onboarding/general.available_units') }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Step 5: plataformas --}}
                    <div class="ob-step ob-hidden" data-step="5">
                        <label class="ob-label"><span class="ob-num">5</span> {{ trans('admin/onboarding/general.platforms') }}</label>
                        <p class="text-muted ob-help">{{ trans('admin/onboarding/general.platforms_help') }}</p>
                        <div class="ob-grid">
                            @foreach ($platforms as $p)
                                <label class="ob-check ob-check-platform">
                                    <input type="checkbox" name="platforms[]" value="{{ $p }}" data-platform="{{ $p }}"
                                           {{ in_array($p, (array) old('platforms', []), true) ? 'checked' : '' }}>
                                    <span class="ob-check-name"><i class="fa-solid fa-cloud"></i> {{ $p }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Sub-bloque: roles de OTM (solo si se marcó OTM) --}}
                        <div id="otm_roles_wrap" class="ob-subblock ob-hidden">
                            <label class="ob-label-sm"><i class="fa-solid fa-user-shield"></i> {{ trans('admin/onboarding/general.otm_roles') }}</label>
                            <p class="ob-help">{{ trans('admin/onboarding/general.otm_roles_help') }}</p>
                            <div class="ob-grid">
                                @foreach ($otmRoles as $role)
                                    <label class="ob-check">
                                        <input type="checkbox" name="otm_roles[]" value="{{ $role }}"
                                               {{ in_array($role, (array) old('otm_roles', []), true) ? 'checked' : '' }}>
                                        <span class="ob-check-name">{{ $role }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Notas + enviar --}}
                    <div class="ob-step ob-hidden" data-step="6">
                        <label for="notes" class="ob-label-sm">{{ trans('admin/onboarding/general.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="{{ trans('admin/onboarding/general.notes_ph') }}">{{ old('notes') }}</textarea>

                        <div style="margin-top:18px;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-paper-plane"></i> {{ trans('admin/onboarding/general.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
.ob-box { border-radius: 12px; }
.ob-step { padding: 16px 0; border-top: 1px dashed #e6eaef; animation: obFade .35s ease; }
.ob-step:first-child { border-top: none; padding-top: 4px; }
.ob-hidden { display: none; }
@keyframes obFade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
.ob-label { display: block; font-weight: 700; font-size: 14px; color: #2d3748; margin-bottom: 8px; }
.ob-label-sm { display: block; font-weight: 600; font-size: 13px; color: #4a5568; margin-bottom: 6px; }
.ob-help { font-size: 12.5px; margin: -2px 0 10px; }
.ob-num {
    display: inline-block; width: 22px; height: 22px; line-height: 22px; text-align: center;
    background: #3c8dbc; color: #fff; border-radius: 50%; font-size: 12px; margin-right: 6px;
}
.ob-choice-group { display: flex; flex-wrap: wrap; gap: 12px; }
.ob-choice {
    flex: 1 1 200px; display: flex; align-items: center; gap: 8px; cursor: pointer;
    border: 2px solid #e6eaef; border-radius: 10px; padding: 14px 16px; margin: 0; font-weight: 600;
    transition: border-color .15s, background .15s;
}
.ob-choice:hover { border-color: #b8c6d6; }
.ob-choice input { margin: 0; }
.ob-choice input:checked ~ span { color: #3c8dbc; }
.ob-choice.is-selected { border-color: #3c8dbc; background: #f2f8fc; }
.ob-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 10px; }
.ob-check {
    display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;
    border: 1px solid #e6eaef; border-radius: 9px; padding: 10px 12px; font-weight: 500;
    transition: border-color .15s, background .15s;
}
.ob-check:hover { border-color: #b8c6d6; }
.ob-check input { margin: 0; }
.ob-check.is-selected { border-color: #3c8dbc; background: #f2f8fc; }
.ob-check-name { flex: 1 1 auto; }
.ob-subblock { margin-top: 14px; padding: 14px; border: 1px dashed #cfd8e3; border-radius: 10px; background: #f8fafc; animation: obFade .3s ease; }
[data-theme="dark"] .ob-subblock { background: #262d37; border-color: #3d4756; }
.ob-badge { font-size: 11px; font-weight: 600; color: #6b7c93; background: #eef1f5; border-radius: 10px; padding: 1px 8px; white-space: nowrap; }
.ob-select { width: 100%; }
[data-theme="dark"] .ob-label { color: #e5e9ef; }
[data-theme="dark"] .ob-choice, [data-theme="dark"] .ob-check { border-color: #3d4756; }
[data-theme="dark"] .ob-choice.is-selected, [data-theme="dark"] .ob-check.is-selected { background: #23303a; border-color: #3c8dbc; }
[data-theme="dark"] .ob-badge { background: #333a45; color: #cfd6df; }
</style>
@endpush

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    (function () {
        var form = document.getElementById('ob-form');
        if (!form) return;

        var hasSelect2 = window.jQuery && jQuery.fn.select2;
        function initSelect2(el) {
            if (!el || !hasSelect2 || el.dataset.s2 === '1') return;
            jQuery(el).select2({ width: '100%', allowClear: true });
            el.dataset.s2 = '1';
        }

        function stepEl(name) { return form.querySelector('.ob-step[data-step="' + name + '"]'); }
        function show(name) {
            var el = stepEl(name); if (!el) return;
            el.classList.remove('ob-hidden');
            // Lazily init select2 once the step (and its width) is visible.
            el.querySelectorAll('select.ob-select').forEach(initSelect2);
        }
        function hide(name) { var el = stepEl(name); if (el) el.classList.add('ob-hidden'); }

        var position = document.getElementById('position');
        var name = document.getElementById('employee_name');
        var assetInput = document.getElementById('asset_id_input');
        var newAsset = document.getElementById('new_asset');
        var replaceAsset = document.getElementById('replace_asset');
        var replaceUser = document.getElementById('replace_user');
        var activeAsset = null; // which select currently drives asset_id

        function syncAsset() { if (assetInput) assetInput.value = activeAsset ? (activeAsset.value || '') : ''; }

        // Step 1 -> 2
        position.addEventListener('input', function () {
            if (position.value.trim()) { show('2'); }
        });
        // Step 2 -> 3
        name.addEventListener('input', function () {
            if (name.value.trim()) { show('3'); }
        });

        function selectEntry(type) {
            form.querySelectorAll('.ob-choice').forEach(function (c) {
                c.classList.toggle('is-selected', c.querySelector('input').value === type);
            });
            if (type === 'reemplazo') {
                show('replace'); hide('new');
                activeAsset = replaceAsset;
            } else {
                show('new'); hide('replace');
                activeAsset = newAsset;
            }
            syncAsset();
            show('4'); show('5'); show('6');
        }

        form.querySelectorAll('input[name="entry_type"]').forEach(function (r) {
            r.addEventListener('change', function () { if (r.checked) selectEntry(r.value); });
        });

        // Keep the hidden asset_id in sync with whichever selector is active.
        if (hasSelect2) {
            jQuery(newAsset).on('change', function () { if (activeAsset === newAsset) syncAsset(); });
            jQuery(replaceAsset).on('change', function () { if (activeAsset === replaceAsset) syncAsset(); });
        } else {
            if (newAsset) newAsset.addEventListener('change', function () { if (activeAsset === newAsset) syncAsset(); });
            if (replaceAsset) replaceAsset.addEventListener('change', function () { if (activeAsset === replaceAsset) syncAsset(); });
        }

        // Replacement: fetch the assets assigned to the selected user.
        var msg = document.getElementById('replace_device_msg');
        var userAssetsUrl = "{{ url('ingreso-personal/usuario-equipos') }}";

        function loadUserAssets(userId) {
            if (!replaceAsset) return;
            replaceAsset.innerHTML = '<option value=""></option>';
            if (msg) { msg.style.display = ''; msg.textContent = "{{ trans('admin/onboarding/general.loading_devices') }}"; }
            fetch(userAssetsUrl + '/' + userId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var assets = (data && data.assets) || [];
                    if (!assets.length) {
                        if (msg) { msg.style.display = ''; msg.textContent = "{{ trans('admin/onboarding/general.no_assigned_device') }}"; }
                    } else {
                        if (msg) msg.style.display = 'none';
                        assets.forEach(function (a) {
                            var opt = document.createElement('option');
                            opt.value = a.id; opt.textContent = a.text; opt.selected = true;
                            replaceAsset.appendChild(opt);
                        });
                    }
                    if (window.jQuery) jQuery(replaceAsset).trigger('change');
                })
                .catch(function () {
                    if (msg) { msg.style.display = ''; msg.textContent = "{{ trans('admin/onboarding/general.no_assigned_device') }}"; }
                });
        }

        if (replaceUser) {
            jQuery(replaceUser).on('change', function () {
                var val = replaceUser.value;
                if (val) loadUserAssets(val);
            });
        }

        // Visual "selected" state for accessory/platform checkboxes.
        form.querySelectorAll('.ob-check input[type="checkbox"]').forEach(function (cb) {
            function sync() { cb.closest('.ob-check').classList.toggle('is-selected', cb.checked); }
            cb.addEventListener('change', sync); sync();
        });

        // When OTM is requested, reveal its roles sub-block.
        var otmWrap = document.getElementById('otm_roles_wrap');
        var otmCheckbox = form.querySelector('input[data-platform="OTM"]');
        function toggleOtmRoles() {
            if (!otmWrap || !otmCheckbox) return;
            otmWrap.classList.toggle('ob-hidden', !otmCheckbox.checked);
            if (!otmCheckbox.checked) {
                // Clear role selections when OTM is unchecked.
                otmWrap.querySelectorAll('input[type="checkbox"]').forEach(function (c) {
                    c.checked = false; c.closest('.ob-check').classList.remove('is-selected');
                });
            }
        }
        if (otmCheckbox) { otmCheckbox.addEventListener('change', toggleOtmRoles); toggleOtmRoles(); }

        // Restore state on validation error (old input).
        if (position.value.trim()) show('2');
        if (name.value.trim()) show('3');
        var checkedEntry = form.querySelector('input[name="entry_type"]:checked');
        if (checkedEntry) {
            selectEntry(checkedEntry.value);
            if (checkedEntry.value === 'reemplazo' && replaceUser && replaceUser.value) loadUserAssets(replaceUser.value);
        }
    })();
</script>
@stop
