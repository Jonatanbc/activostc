@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.damages_by_model_report') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('reports/damages') }}" class="btn btn-default pull-right">
        {{ trans('admin/damages/general.damages_report') }}
    </a>
@stop

@section('content')
    <x-container>
        <x-box>
            <p class="text-muted" style="padding: 0 12px;">
                {{ trans('admin/damages/general.by_model_help') }}
            </p>
            <x-table
                nosticky="true"
                name="damagesByModel"
                api_url="{{ route('api.damages.bymodel', ['only_pending' => 'true']) }}"
                :presenter="\App\Presenters\DamagesPresenter::byModelLayout()"
                export_filename="export-damages-by-model-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
