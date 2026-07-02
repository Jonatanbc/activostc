@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.damages_report') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('reports/damages_by_model') }}" class="btn btn-default pull-right">
        {{ trans('admin/damages/general.damages_by_model_report') }}
    </a>
@stop

@section('content')
    <x-container>
        <x-box>
            <x-table
                nosticky="true"
                name="damagesReport"
                api_url="{{ route('api.damages.index', ['report' => 'flat']) }}"
                :presenter="\App\Presenters\DamagesPresenter::reportLayout()"
                export_filename="export-damages-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
