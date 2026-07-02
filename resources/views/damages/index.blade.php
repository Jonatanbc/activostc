@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.all_damages') }}
    @parent
@stop

@section('header_right')
    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#emailListModal">
        <i class="fa-regular fa-envelope"></i> {{ trans('admin/damages/general.email_report') }}
    </button>
@stop

@section('content')
<div class="damages-module">

    {{-- Count per component (damage type) --}}
    @if ($byType->count())
        <div class="component-counts">
            @foreach ($byType as $c)
                <div class="count-card" title="{{ $c->name }}">
                    <span class="count-num">{{ number_format($c->qty) }}</span>
                    <span class="count-name">{{ $c->name }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Summary cards --}}
    <div class="row damage-stats">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-a">
                <i class="stat-icon fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                <div class="stat-value">{{ number_format($stats['damaged_components']) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.card_damaged_components') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-b">
                <i class="stat-icon fa-solid fa-scale-balanced" aria-hidden="true"></i>
                <div class="stat-value">{{ \App\Helpers\Helper::formatCurrencyOutput($stats['avg_cost'] ?? 0) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.card_avg_cost') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-c">
                <i class="stat-icon fa-solid fa-money-bills" aria-hidden="true"></i>
                <div class="stat-value">{{ \App\Helpers\Helper::formatCurrencyOutput($stats['total_cost'] ?? 0) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.card_total_cost') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card stat-d">
                <i class="stat-icon fa-solid fa-file-invoice-dollar" aria-hidden="true"></i>
                <div class="stat-value">{{ number_format($stats['purchase_requests']) }}</div>
                <div class="stat-label">{{ trans('admin/damages/general.card_purchase_requests') }}</div>
            </div>
        </div>
    </div>

    <x-container>
        <x-box>

            {{-- Bulk-actions toolbar (relocated above the table by bootstrap-table) --}}
            <div id="damagesListToolbar" class="hidden-print">
                @can('update', \App\Models\Asset::class)
                    <form method="POST" action="{{ route('purchase-requests.store') }}" id="damagesListForm" class="form-inline" style="display:inline-block;">
                        @csrf
                        <input type="hidden" name="sort" value="asset_damages.id">
                        <input type="hidden" name="order" value="asc">
                        <button type="submit" class="btn btn-warning" id="damagesListButton" disabled>
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            {{ trans('admin/damages/general.generate_purchase_request') }}
                        </button>
                    </form>
                @endcan
            </div>

            <x-table
                name="damagesList"
                :presenter="\App\Presenters\DamagesPresenter::listLayout()"
                api_url="{{ route('api.damages.index') }}"
                show_search="true"
                export_filename="export-damages-{{ date('Y-m-d') }}"
            />
        </x-box>

        {{-- Scheduled email sends --}}
        <x-box>
            <h3 class="box-title" style="padding-left:4px;"><i class="fa-regular fa-clock"></i> {{ trans('admin/damages/general.scheduled_sends') }}</h3>
            @if (isset($schedules) && $schedules->count())
                <div class="table-responsive" style="margin-top:10px;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('admin/damages/general.recipients') }}</th>
                                <th>{{ trans('admin/damages/general.frequency') }}</th>
                                <th>{{ trans('admin/damages/general.day') }}</th>
                                <th>{{ trans('admin/damages/general.time') }}</th>
                                <th>{{ trans('admin/damages/general.next_send') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $s)
                                <tr>
                                    <td>{{ $s->recipients }}</td>
                                    <td>{{ trans('admin/damages/general.freq_'.$s->frequency) }}</td>
                                    <td>{{ ucfirst($s->dayLabel()) }}</td>
                                    <td>{{ $s->send_time ?? '—' }}</td>
                                    <td>{{ $s->nextRunColombia()?->format('Y-m-d H:i') }}</td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('damages.list.schedule_delete', $s) }}"
                                              onsubmit="return confirm('{{ trans('general.delete') }}?');" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted" style="padding:10px 4px;">{{ trans('admin/damages/general.no_schedules') }}</p>
            @endif
        </x-box>
    </x-container>
</div>

{{-- Email / schedule modal --}}
<div class="modal fade" id="emailListModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('damages.list.email') }}">
                @csrf
                <input type="hidden" name="only_pending" value="false">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ trans('admin/damages/general.email_report_title') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="list_recipients">{{ trans('admin/damages/general.recipients') }}</label>
                        <input type="text" name="recipients" id="list_recipients" class="form-control" required
                               placeholder="correo1@empresa.com, correo2@empresa.com" value="{{ old('recipients') }}">
                        <p class="help-block">{{ trans('admin/damages/general.recipients_hint') }}</p>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group">
                                <label for="list_frequency">{{ trans('admin/damages/general.frequency') }}</label>
                                <select name="frequency" id="list_frequency" class="form-control">
                                    <option value="weekly">{{ trans('admin/damages/general.freq_weekly') }}</option>
                                    <option value="biweekly">{{ trans('admin/damages/general.freq_biweekly') }}</option>
                                    <option value="monthly">{{ trans('admin/damages/general.freq_monthly') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-7">
                            <div class="form-group">
                                <label for="list_send_day">{{ trans('admin/damages/general.send_day') }}</label>
                                <select name="send_day" id="list_send_day" class="form-control">
                                    @foreach ([1, 2, 3, 4, 5, 6, 0] as $d)
                                        <option value="{{ $d }}" {{ (string) old('send_day', 1) === (string) $d ? 'selected' : '' }}>
                                            {{ ucfirst(\Carbon\Carbon::now()->startOfWeek(\Carbon\CarbonInterface::SUNDAY)->addDays($d)->translatedFormat('l')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-5">
                            <div class="form-group">
                                <label for="list_send_time">{{ trans('admin/damages/general.send_time') }}</label>
                                <input type="time" name="send_time" id="list_send_time" class="form-control" value="{{ old('send_time', '08:00') }}">
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <p class="help-block" style="margin-top:-6px;"><i class="fa-regular fa-clock"></i> {{ trans('admin/damages/general.timezone_note') }}</p>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-paper-plane"></i> {{ trans('admin/damages/general.send_now') }}
                    </button>
                    <button type="submit" class="btn btn-primary" formaction="{{ route('damages.list.schedule') }}">
                        <i class="fa-regular fa-clock"></i> {{ trans('admin/damages/general.program_sending') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit damage modal (loads the edit form in an iframe so photo uploads keep working) --}}
<div class="modal fade" id="damageEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="overflow:hidden;">
            {{-- No white header: the dark hero inside the form acts as the title. Floating close button. --}}
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    style="position:absolute; right:14px; top:11px; z-index:20; color:#fff; opacity:.85; text-shadow:none; font-size:26px; line-height:1;">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body" style="padding:0;">
                <iframe id="damageEditFrame" src="about:blank" style="width:100%; height:80vh; max-height:620px; border:0; display:block;"></iframe>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
/* Per-component count cards */
.damages-module .component-counts {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-bottom: 10px;
}
.damages-module .count-card {
    flex: 1 1 auto;
    min-width: 78px;
    display: flex;
    flex-direction: row;
    align-items: baseline;
    justify-content: center;
    gap: 5px;
    padding: 6px 10px;
    background: #fff;
    border: 1px solid #e2e7ee;
    border-top: 2px solid #5b6b80;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(17, 24, 39, .05);
    transition: transform .15s ease, box-shadow .15s ease;
}
.damages-module .count-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(17, 24, 39, .10);
}
.damages-module .count-card .count-num {
    font-size: 16px;
    font-weight: 700;
    line-height: 1;
    color: #2c333e;
}
.damages-module .count-card .count-name {
    font-size: 11px;
    font-weight: 600;
    color: #7b8794;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}
[data-theme="dark"] .damages-module .count-card {
    background: #2b303a;
    border-color: #3d4756;
    border-top-color: #6a7a8c;
    box-shadow: 0 3px 10px rgba(0, 0, 0, .4);
}
[data-theme="dark"] .damages-module .count-card .count-num { color: #e5e9ef; }
[data-theme="dark"] .damages-module .count-card .count-name { color: #9aa5b1; }

/* White column-header text on a dark header background */
.damages-module .table > thead > tr > th,
.damages-module .fixed-table-header thead th,
.damages-module .fixed-table-header thead th .th-inner,
.damages-module .fixed-table-container thead th,
.damages-module .fixed-table-container thead th .th-inner {
    background-color: #2c333e !important;
    color: #fff !important;
    border-color: #2c333e !important;
}
.damages-module .fixed-table-container thead th a { color: #fff !important; }

.damages-module .damage-stats { margin-bottom: 2px; }
.damages-module .stat-card {
    position: relative;
    min-height: 64px;
    padding: 10px 12px 10px 14px;
    border-radius: 11px;
    color: #fff;
    overflow: hidden;
    margin-bottom: 10px;
    box-shadow: 0 3px 10px rgba(17, 24, 39, .10);
}
.damages-module .stat-card .stat-value {
    font-size: 19px;
    font-weight: 700;
    line-height: 1.05;
    text-shadow: 0 1px 2px rgba(0, 0, 0, .18);
}
.damages-module .stat-card .stat-label {
    font-size: 11px;
    font-weight: 600;
    opacity: .95;
    margin-top: 1px;
}
.damages-module .stat-card .stat-icon {
    position: absolute;
    top: 10px;
    right: 11px;
    font-size: 22px;
    opacity: .26;
}
.damages-module .stat-a { background: linear-gradient(135deg, #2c333e 0%, #3b4552 100%); }
.damages-module .stat-b { background: linear-gradient(135deg, #3c4450 0%, #4d586a 100%); }
.damages-module .stat-c { background: linear-gradient(135deg, #454e5a 0%, #566475 100%); }
.damages-module .stat-d { background: linear-gradient(135deg, #7b4397 0%, #a45bc9 100%); }

[data-theme="dark"] .damages-module .stat-card { box-shadow: 0 6px 18px rgba(0, 0, 0, .45); }

#damagesListToolbar { padding: 4px 0 8px; }
</style>
@endpush

@section('moar_scripts')
@include ('partials.bootstrap-table')

<script nonce="{{ csrf_token() }}">
    // Open the "edit damage" action in a modal (iframe) instead of navigating away.
    $(document).on('click', '#damagesListListingTable a[href*="/damages/"][href$="/edit"]', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        url += (url.indexOf('?') > -1 ? '&' : '?') + 'modal=1';
        $('#damageEditFrame').attr('src', url);
        $('#damageEditModal').modal('show');
    });

    // Free the iframe when the modal closes (stops any playing content / resets state).
    $('#damageEditModal').on('hidden.bs.modal', function () {
        $('#damageEditFrame').attr('src', 'about:blank');
    });

    // The edit form (inside the iframe) posts messages back to us.
    window.addEventListener('message', function (ev) {
        if (ev.data === 'damage-saved') {
            $('#damageEditModal').modal('hide');
            $('#damagesListListingTable').bootstrapTable('refresh');
        } else if (ev.data === 'damage-cancel') {
            $('#damageEditModal').modal('hide');
        }
    });
</script>
@stop

@if ($errors->any())
@push('js')
<script nonce="{{ csrf_token() }}">
    $(function () { $('#emailListModal').modal('show'); });
</script>
@endpush
@endif
