@extends('layouts/default')

@section('title')
    {{ trans('admin/onboarding/general.management_title') }}
    @parent
@stop

@section('content')
@php
    $tabs = [
        'pending' => 'status_pending',
        'approved' => 'status_approved',
        'fulfilled' => 'status_fulfilled',
        'rejected' => 'status_rejected',
    ];
    $statusLabel = [
        'pending' => 'label-warning',
        'approved' => 'label-info',
        'fulfilled' => 'label-success',
        'rejected' => 'label-default',
    ];
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/onboarding/general.management_title') }}</h2>
                <div class="pull-right">
                    <a href="{{ route('onboarding.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-user-plus"></i> {{ trans('admin/onboarding/general.new_request') }}
                    </a>
                </div>
            </div>
            <div class="box-body">
                <ul class="nav nav-tabs" style="margin-bottom:14px;">
                    @foreach ($tabs as $key => $label)
                        <li class="{{ $status === $key ? 'active' : '' }}">
                            <a href="{{ route('onboarding.index', ['status' => $key]) }}">
                                {{ trans('admin/onboarding/general.'.$label) }}
                                <span class="badge">{{ $counts[$key] ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('admin/onboarding/general.col_employee') }}</th>
                                <th>{{ trans('admin/onboarding/general.col_type') }}</th>
                                <th>{{ trans('admin/onboarding/general.col_device') }}</th>
                                <th>{{ trans('admin/onboarding/general.col_components') }}</th>
                                <th>{{ trans('admin/onboarding/general.col_platforms') }}</th>
                                <th>{{ trans('admin/onboarding/general.col_requested_by') }}</th>
                                <th>{{ trans('admin/onboarding/general.col_status') }}</th>
                                <th class="text-right">{{ trans('button.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $r)
                                <tr>
                                    <td>
                                        <strong>{{ $r->employee_name }}</strong>
                                        <br><small class="text-muted">{{ $r->position }}</small>
                                    </td>
                                    <td>
                                        @if ($r->entry_type === 'reemplazo')
                                            <span class="label label-info">{{ trans('admin/onboarding/general.entry_replacement') }}</span>
                                            @if ($r->replacesUser)
                                                <br><small class="text-muted">{{ trans('admin/onboarding/general.replaces_short') }}: {{ trim($r->replacesUser->first_name.' '.$r->replacesUser->last_name) ?: $r->replacesUser->username }}</small>
                                            @endif
                                        @else
                                            <span class="label label-primary">{{ trans('admin/onboarding/general.entry_new') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->asset)
                                            <a href="{{ route('hardware.show', $r->asset->id) }}">{{ $r->asset->asset_tag ?: ('#'.$r->asset->id) }}</a>
                                            <br><small class="text-muted">{{ optional($r->asset->model)->name }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $accs = $r->accessories ?? []; @endphp
                                        @if (empty($accs))
                                            <span class="text-muted">{{ trans('admin/onboarding/general.none') }}</span>
                                        @else
                                            @foreach ($accs as $aid)
                                                <span class="label label-default" style="display:inline-block;margin:1px;">{{ $accessoryNames[$aid] ?? ('#'.$aid) }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        @php $plats = $r->platforms ?? []; @endphp
                                        @if (empty($plats))
                                            <span class="text-muted">{{ trans('admin/onboarding/general.none') }}</span>
                                        @else
                                            @foreach ($plats as $p)
                                                <span class="label label-primary" style="display:inline-block;margin:1px;">{{ $p }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        {{ $r->requestedBy ? trim($r->requestedBy->first_name.' '.$r->requestedBy->last_name) : '—' }}
                                        <br><small class="text-muted">{{ $r->created_at ? $r->created_at->format('Y-m-d H:i') : '' }}</small>
                                    </td>
                                    <td><span class="label {{ $statusLabel[$r->status] ?? 'label-default' }}">{{ trans('admin/onboarding/general.status_'.$r->status) }}</span></td>
                                    <td class="text-right" style="white-space:nowrap;">
                                        @if ($r->notes)
                                            <button type="button" class="btn btn-xs btn-default" data-toggle="tooltip" title="{{ e($r->notes) }}"><i class="fa-solid fa-comment"></i></button>
                                        @endif
                                        @can('update', \App\Models\Asset::class)
                                            @if ($r->status !== 'fulfilled')
                                                <form method="post" action="{{ route('onboarding.status', $r->id) }}" style="display:inline;">
                                                    {{ csrf_field() }} {{ method_field('PATCH') }}
                                                    <input type="hidden" name="status" value="fulfilled">
                                                    <button class="btn btn-xs btn-success" title="{{ trans('admin/onboarding/general.action_fulfill') }}"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            @endif
                                            @if ($r->status === 'pending')
                                                <form method="post" action="{{ route('onboarding.status', $r->id) }}" style="display:inline;">
                                                    {{ csrf_field() }} {{ method_field('PATCH') }}
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-xs btn-warning" title="{{ trans('admin/onboarding/general.action_reject') }}"><i class="fa-solid fa-ban"></i></button>
                                                </form>
                                            @endif
                                            @if ($r->status !== 'pending')
                                                <form method="post" action="{{ route('onboarding.status', $r->id) }}" style="display:inline;">
                                                    {{ csrf_field() }} {{ method_field('PATCH') }}
                                                    <input type="hidden" name="status" value="pending">
                                                    <button class="btn btn-xs btn-default" title="{{ trans('admin/onboarding/general.status_pending') }}"><i class="fa-solid fa-rotate-left"></i></button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('delete', \App\Models\Asset::class)
                                            <form method="post" action="{{ route('onboarding.destroy', $r->id) }}" style="display:inline;"
                                                  onsubmit="return confirm('{{ trans('admin/onboarding/general.confirm_delete') }}');">
                                                {{ csrf_field() }} {{ method_field('DELETE') }}
                                                <button class="btn btn-xs btn-danger" title="{{ trans('admin/onboarding/general.action_delete') }}"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted" style="padding:24px;">{{ trans('admin/onboarding/general.no_requests') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
