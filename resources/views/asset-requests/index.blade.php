@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.requests_module') }}
    @parent
@stop

@section('content')
@php
    $tabs = [
        'pending' => 'req_status_pending',
        'approved' => 'req_status_approved',
        'fulfilled' => 'req_status_fulfilled',
        'rejected' => 'req_status_rejected',
    ];
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/damages/general.requests_module') }}</h2>
            </div>
            <div class="box-body">
                <p class="text-muted" style="margin-top:-6px;">{{ trans('admin/damages/general.requests_help') }}</p>

                <ul class="nav nav-tabs" style="margin-bottom:14px;">
                    @foreach ($tabs as $key => $label)
                        <li class="{{ $status === $key ? 'active' : '' }}">
                            <a href="{{ route('assets.requests', ['status' => $key]) }}">
                                {{ trans('admin/damages/general.'.$label) }}
                                <span class="badge">{{ $counts[$key] ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('admin/damages/general.req_equipment') }}</th>
                                <th>{{ trans('admin/damages/general.assignee_name') }}</th>
                                <th>{{ trans('admin/damages/general.assignee_id_number') }}</th>
                                <th>{{ trans('admin/damages/general.assignee_position') }}</th>
                                <th>{{ trans('admin/damages/general.assignee_location') }}</th>
                                <th>{{ trans('admin/damages/general.account_type') }}</th>
                                <th>{{ trans('admin/damages/general.needed_at') }}</th>
                                <th>{{ trans('admin/damages/general.req_requested_by') }}</th>
                                <th class="text-right">{{ trans('button.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $r)
                                <tr>
                                    <td>
                                        @if ($r->asset)
                                            <a href="{{ route('hardware.show', $r->asset->id) }}">{{ $r->asset->asset_tag ?: ('#'.$r->asset->id) }}</a>
                                            <br><small class="text-muted">{{ optional($r->asset->model)->name }}</small>
                                        @else — @endif
                                    </td>
                                    <td>{{ $r->assignee_name }}</td>
                                    <td>{{ $r->assignee_id_number ?: '—' }}</td>
                                    <td>{{ $r->assignee_position ?: '—' }}</td>
                                    <td>{{ $r->assignee_location ?: '—' }}</td>
                                    <td>
                                        @if ($r->account_type === 'reemplazo')
                                            <span class="label label-info">{{ trans('admin/damages/general.account_replacement') }}</span>
                                            @if ($r->replaces_person)<br><small class="text-muted">{{ $r->replaces_person }}</small>@endif
                                        @elseif ($r->account_type === 'nueva')
                                            <span class="label label-primary">{{ trans('admin/damages/general.account_new') }}</span>
                                        @else — @endif
                                    </td>
                                    <td>{{ $r->needed_at ? $r->needed_at->format('Y-m-d') : '—' }}</td>
                                    <td>
                                        {{ $r->requestedBy ? trim($r->requestedBy->first_name.' '.$r->requestedBy->last_name) : '—' }}
                                        <br><small class="text-muted">{{ $r->created_at ? $r->created_at->format('Y-m-d H:i') : '' }}</small>
                                    </td>
                                    <td class="text-right" style="white-space:nowrap;">
                                        @if ($r->justification)
                                            <button type="button" class="btn btn-xs btn-default" data-toggle="tooltip" title="{{ e($r->justification) }}"><i class="fa-solid fa-comment"></i></button>
                                        @endif
                                        @can('update', \App\Models\Asset::class)
                                            @if ($r->status !== 'fulfilled')
                                                <form method="post" action="{{ route('assets.requests.status', $r->id) }}" style="display:inline;">
                                                    {{ csrf_field() }} {{ method_field('PATCH') }}
                                                    <input type="hidden" name="status" value="fulfilled">
                                                    <button class="btn btn-xs btn-success" title="{{ trans('admin/damages/general.req_fulfill') }}"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            @endif
                                            @if ($r->status === 'pending')
                                                <form method="post" action="{{ route('assets.requests.status', $r->id) }}" style="display:inline;">
                                                    {{ csrf_field() }} {{ method_field('PATCH') }}
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-xs btn-warning" title="{{ trans('admin/damages/general.req_reject') }}"><i class="fa-solid fa-ban"></i></button>
                                                </form>
                                            @endif
                                            @if ($r->status !== 'pending')
                                                <form method="post" action="{{ route('assets.requests.status', $r->id) }}" style="display:inline;">
                                                    {{ csrf_field() }} {{ method_field('PATCH') }}
                                                    <input type="hidden" name="status" value="pending">
                                                    <button class="btn btn-xs btn-default" title="{{ trans('admin/damages/general.req_reopen') }}"><i class="fa-solid fa-rotate-left"></i></button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('delete', \App\Models\Asset::class)
                                            <form method="post" action="{{ route('assets.requests.destroy', $r->id) }}" style="display:inline;"
                                                  onsubmit="return confirm('{{ trans('admin/damages/general.confirm_delete_asset_request') }}');">
                                                {{ csrf_field() }} {{ method_field('DELETE') }}
                                                <button class="btn btn-xs btn-danger"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted" style="padding:24px;">{{ trans('admin/damages/general.no_requests_module') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
