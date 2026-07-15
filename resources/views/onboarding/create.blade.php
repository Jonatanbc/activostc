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
.ob-box {
    --ob-accent: #4f7cff;
    --ob-accent-2: #6a5cff;
    --ob-accent-soft: #eef2ff;
    --ob-border: #e7ebf3;
    --ob-ink: #1f2937;
    --ob-muted: #8a94a6;
    border: 1px solid var(--ob-border);
    border-radius: 18px;
    box-shadow: 0 12px 32px -12px rgba(31, 41, 55, .18), 0 2px 6px rgba(31, 41, 55, .05);
    overflow: hidden;
}
.ob-box > .box-header {
    background: linear-gradient(135deg, var(--ob-accent), var(--ob-accent-2));
    border-bottom: none; padding: 20px 22px;
}
.ob-box > .box-header .box-title { color: #fff; font-weight: 700; font-size: 18px; letter-spacing: .2px; }
.ob-box > .box-header .box-title i { margin-right: 8px; opacity: .9; }
.ob-box > .box-body { padding: 8px 24px 24px; }
.ob-box > .box-body > .text-muted:first-child { margin: 14px 0 4px; font-size: 13.5px; }

.ob-step { padding: 20px 0; border-top: 1px solid #eef1f6; animation: obFade .4s cubic-bezier(.2,.7,.3,1); }
.ob-step:first-child { border-top: none; padding-top: 12px; }
.ob-hidden { display: none; }
@keyframes obFade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

.ob-label { display: flex; align-items: center; font-weight: 700; font-size: 14.5px; color: var(--ob-ink); margin-bottom: 10px; }
.ob-label-sm { display: block; font-weight: 600; font-size: 13px; color: #4a5568; margin-bottom: 7px; }
.ob-help { font-size: 12.5px; color: var(--ob-muted); margin: -4px 0 12px; }
.ob-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; margin-right: 10px; flex: 0 0 auto;
    background: linear-gradient(135deg, var(--ob-accent), var(--ob-accent-2));
    color: #fff; border-radius: 50%; font-size: 12.5px; font-weight: 700;
    box-shadow: 0 4px 10px -2px rgba(79, 124, 255, .5);
}

/* Text inputs / textarea */
.ob-box .form-control {
    border-radius: 11px; border-color: var(--ob-border); box-shadow: none;
    min-height: 42px; transition: border-color .15s, box-shadow .15s;
}
.ob-box textarea.form-control { min-height: auto; }
.ob-box .form-control:focus {
    border-color: var(--ob-accent);
    box-shadow: 0 0 0 4px rgba(79, 124, 255, .14);
}

/* Big radio-cards: nuevo vs reemplazo */
.ob-choice-group { display: flex; flex-wrap: wrap; gap: 14px; }
.ob-choice {
    position: relative; flex: 1 1 220px; display: flex; align-items: center; gap: 12px; cursor: pointer;
    border: 1.5px solid var(--ob-border); border-radius: 14px; padding: 18px 44px 18px 18px; margin: 0;
    font-weight: 600; color: #3b465c; background: #fff;
    transition: transform .12s, border-color .15s, box-shadow .2s, background .15s;
}
.ob-choice span { display: flex; align-items: center; gap: 10px; }
.ob-choice span i { color: var(--ob-accent); font-size: 16px; }
.ob-choice:hover { border-color: #c3cede; transform: translateY(-2px); box-shadow: 0 10px 22px -14px rgba(31,41,55,.4); }
.ob-choice input { position: absolute; opacity: 0; pointer-events: none; }
.ob-choice.is-selected { border-color: var(--ob-accent); background: var(--ob-accent-soft); box-shadow: 0 8px 20px -12px rgba(79,124,255,.6); }
.ob-choice.is-selected::after {
    content: "\2713"; position: absolute; top: 50%; right: 15px; transform: translateY(-50%);
    width: 22px; height: 22px; line-height: 22px; text-align: center;
    background: var(--ob-accent); color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700;
}

/* Toggle-pill checkboxes: accesorios / plataformas / roles */
.ob-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
.ob-check {
    position: relative; display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;
    border: 1.5px solid var(--ob-border); border-radius: 12px; padding: 12px 14px 12px 46px;
    font-weight: 500; color: #3b465c; background: #fff;
    transition: transform .12s, border-color .15s, box-shadow .2s, background .15s;
}
.ob-check:hover { border-color: #c3cede; transform: translateY(-1px); box-shadow: 0 8px 18px -14px rgba(31,41,55,.4); }
.ob-check input { position: absolute; opacity: 0; pointer-events: none; }
.ob-check::before {
    content: ""; position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    width: 20px; height: 20px; border: 2px solid #cbd5e1; border-radius: 6px; background: #fff;
    transition: background .15s, border-color .15s;
}
.ob-check.is-selected { border-color: var(--ob-accent); background: var(--ob-accent-soft); }
.ob-check.is-selected::before { background: var(--ob-accent); border-color: var(--ob-accent); }
.ob-check.is-selected::after {
    content: "\2713"; position: absolute; left: 18px; top: 50%; transform: translateY(-55%);
    color: #fff; font-size: 12px; font-weight: 700;
}
.ob-check-name { flex: 1 1 auto; }
.ob-check-name i { color: var(--ob-accent); margin-right: 4px; }
.ob-badge { font-size: 11px; font-weight: 700; color: #5568a8; background: #e9edfb; border-radius: 20px; padding: 2px 9px; white-space: nowrap; }

.ob-subblock {
    margin-top: 16px; padding: 16px 18px; border: 1px solid var(--ob-border);
    border-left: 3px solid var(--ob-accent); border-radius: 12px;
    background: linear-gradient(180deg, var(--ob-accent-soft), #fff); animation: obFade .3s ease;
}

.ob-select { width: 100%; }
.ob-box .select2-container--default .select2-selection--single { height: 42px; border-radius: 11px; border-color: var(--ob-border); }
.ob-box .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
.ob-box .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }

.ob-box .btn.btn-lg[type="submit"] {
    background: linear-gradient(135deg, var(--ob-accent), var(--ob-accent-2)); border: none;
    border-radius: 12px; padding: 12px 26px; font-weight: 700; letter-spacing: .2px;
    box-shadow: 0 12px 24px -10px rgba(79, 124, 255, .7); transition: transform .12s, box-shadow .2s;
}
.ob-box .btn.btn-lg[type="submit"]:hover { transform: translateY(-2px); box-shadow: 0 16px 30px -10px rgba(79, 124, 255, .8); }

/* Dark theme */
[data-theme="dark"] .ob-box {
    --ob-border: #3a4453; --ob-ink: #e5e9ef; --ob-muted: #94a0b3; --ob-accent-soft: #232c46;
    background: #232830; box-shadow: 0 12px 32px -14px rgba(0,0,0,.6);
}
[data-theme="dark"] .ob-step { border-top-color: #333b47; }
[data-theme="dark"] .ob-choice, [data-theme="dark"] .ob-check { background: #2a303a; color: #cfd6df; }
[data-theme="dark"] .ob-check::before { background: #2a303a; border-color: #4a5563; }
[data-theme="dark"] .ob-choice.is-selected, [data-theme="dark"] .ob-check.is-selected { background: var(--ob-accent-soft); border-color: var(--ob-accent); }
[data-theme="dark"] .ob-badge { background: #313a4d; color: #aeb9e0; }
[data-theme="dark"] .ob-subblock { background: linear-gradient(180deg, #232c46, #232830); }
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
