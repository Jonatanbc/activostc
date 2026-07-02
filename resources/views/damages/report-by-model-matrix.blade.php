@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.damages_by_model_matrix') }}
    @parent
@stop

@section('header_right')
    <button type="button" class="btn btn-primary pull-right" style="margin-left:6px;" data-toggle="modal" data-target="#emailReportModal">
        <i class="fa-regular fa-envelope"></i> {{ trans('admin/damages/general.email_report') }}
    </button>
    <a href="{{ route('reports/damages_by_model') }}" class="btn btn-default pull-right" style="margin-left:6px;">
        {{ trans('admin/damages/general.damages_by_model_report') }}
    </a>
    <a href="{{ route('reports/damages') }}" class="btn btn-default pull-right">
        {{ trans('admin/damages/general.damages_report') }}
    </a>
@stop

@section('content')
<x-container>
    <x-box>
        <div class="clearfix" style="padding: 0 4px 12px;">
            <p class="text-muted pull-left" style="margin:6px 0;">
                {{ trans('admin/damages/general.by_model_matrix_help') }}
            </p>
            <div class="btn-group pull-right" role="group">
                <a href="{{ route('reports/damages_by_model_summary') }}"
                   class="btn btn-sm {{ !$onlyPending ? 'btn-primary' : 'btn-default' }}">
                    {{ trans('admin/damages/general.show_all') }}
                </a>
                <a href="{{ route('reports/damages_by_model_summary', ['only_pending' => 'true']) }}"
                   class="btn btn-sm {{ $onlyPending ? 'btn-primary' : 'btn-default' }}">
                    {{ trans('admin/damages/general.only_pending') }}
                </a>
            </div>
        </div>

        @if (count($rows))
            <div class="table-responsive damage-matrix">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="sticky-col">{{ trans('admin/damages/general.asset_type') }}</th>
                            <th>{{ trans('admin/hardware/form.model') }}</th>
                            @foreach ($types as $type)
                                <th class="text-center">{{ $type }}</th>
                            @endforeach
                            <th class="text-center">{{ trans('admin/damages/general.total') }}</th>
                            <th class="text-right">{{ trans('admin/damages/table.total_cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="sticky-col">{{ $row['category'] ?? '—' }}</td>
                                <td><strong>{{ $row['model'] }}</strong></td>
                                @foreach ($types as $type)
                                    <td class="text-center">
                                        @if ($row['cells'][$type] > 0)
                                            {{ number_format($row['cells'][$type]) }}
                                        @else
                                            <span class="text-muted">·</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center"><strong>{{ number_format($row['total_qty']) }}</strong></td>
                                <td class="text-right">{{ \App\Helpers\Helper::formatCurrencyOutput($row['total_cost']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="sticky-col">{{ trans('admin/damages/general.total') }}</th>
                            <th></th>
                            @foreach ($types as $type)
                                <th class="text-center">{{ number_format($colTotals[$type]) }}</th>
                            @endforeach
                            <th class="text-center">{{ number_format($grandQty) }}</th>
                            <th class="text-right">{{ \App\Helpers\Helper::formatCurrencyOutput($grandCost) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="text-center text-muted" style="padding: 30px 0;">—</p>
        @endif
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
                            <th>{{ trans('admin/damages/general.only_pending') }}</th>
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
                                <td>{{ $s->only_pending ? trans('general.yes') : trans('general.no') }}</td>
                                <td>{{ $s->nextRunColombia()?->format('Y-m-d H:i') }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('reports/damages_by_model_schedule_delete', $s) }}"
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

{{-- Email / schedule modal --}}
<div class="modal fade" id="emailReportModal" tabindex="-1" role="dialog" aria-labelledby="emailReportModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('reports/damages_by_model_email') }}">
                @csrf
                <input type="hidden" name="only_pending" value="{{ $onlyPending ? 'true' : 'false' }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="emailReportModalLabel">{{ trans('admin/damages/general.email_report_title') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipients">{{ trans('admin/damages/general.recipients') }}</label>
                        <input type="text" name="recipients" id="recipients" class="form-control" required
                               placeholder="correo1@empresa.com, correo2@empresa.com" value="{{ old('recipients') }}">
                        <p class="help-block">{{ trans('admin/damages/general.recipients_hint') }}</p>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group {{ $errors->has('frequency') ? 'has-error' : '' }}">
                                <label for="frequency">{{ trans('admin/damages/general.frequency') }}</label>
                                <select name="frequency" id="frequency" class="form-control">
                                    <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>{{ trans('admin/damages/general.freq_weekly') }}</option>
                                    <option value="biweekly" {{ old('frequency') === 'biweekly' ? 'selected' : '' }}>{{ trans('admin/damages/general.freq_biweekly') }}</option>
                                    <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>{{ trans('admin/damages/general.freq_monthly') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-7">
                            <div class="form-group {{ $errors->has('send_day') ? 'has-error' : '' }}">
                                <label for="send_day">{{ trans('admin/damages/general.send_day') }}</label>
                                <select name="send_day" id="send_day" class="form-control">
                                    @foreach ([1, 2, 3, 4, 5, 6, 0] as $d)
                                        <option value="{{ $d }}" {{ (string) old('send_day', 1) === (string) $d ? 'selected' : '' }}>
                                            {{ ucfirst(\Carbon\Carbon::now()->startOfWeek(\Carbon\CarbonInterface::SUNDAY)->addDays($d)->translatedFormat('l')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-5">
                            <div class="form-group {{ $errors->has('send_time') ? 'has-error' : '' }}">
                                <label for="send_time">{{ trans('admin/damages/general.send_time') }}</label>
                                <input type="time" name="send_time" id="send_time" class="form-control" value="{{ old('send_time', '08:00') }}">
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <p class="help-block" style="margin-top:-6px;">
                                <i class="fa-regular fa-clock"></i> {{ trans('admin/damages/general.timezone_note') }}
                            </p>
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
                    <button type="submit" class="btn btn-primary" formaction="{{ route('reports/damages_by_model_schedule') }}">
                        <i class="fa-regular fa-clock"></i> {{ trans('admin/damages/general.program_sending') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
.damage-matrix table { min-width: 100%; }
.damage-matrix th, .damage-matrix td { white-space: nowrap; vertical-align: middle; }
.damage-matrix thead th,
.damage-matrix thead th a {
    background-color: #2c333e !important;
    color: #fff !important;
    border-color: #2c333e !important;
}
.damage-matrix tfoot th { background: #f4f6f9; }
.damage-matrix .sticky-col { position: sticky; left: 0; z-index: 2; }
.damage-matrix thead .sticky-col { background-color: #2c333e !important; color: #fff !important; }
.damage-matrix tbody .sticky-col { background: #fff; }
.damage-matrix tbody tr:nth-of-type(odd) .sticky-col { background: #f9fafc; }
.damage-matrix tfoot .sticky-col { background: #f4f6f9; }
[data-theme="dark"] .damage-matrix tbody .sticky-col { background: #22272e; }
[data-theme="dark"] .damage-matrix tbody tr:nth-of-type(odd) .sticky-col { background: #262c34; }
[data-theme="dark"] .damage-matrix tfoot th { background: #2b303a; }
[data-theme="dark"] .damage-matrix tfoot .sticky-col { background: #2b303a; }
</style>
@endpush

@if ($errors->any())
@push('js')
<script nonce="{{ csrf_token() }}">
    $(function () { $('#emailReportModal').modal('show'); });
</script>
@endpush
@endif
