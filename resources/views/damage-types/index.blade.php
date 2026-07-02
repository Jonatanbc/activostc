@extends('layouts/default')

@section('title')
    {{ trans('admin/damages/general.damage_types') }}
    @parent
@stop

@section('content')
    <x-container>
        <x-box>
            <x-table
                name="damagetype"
                buttons="damageTypeButtons"
                api_url="{{ route('api.damage-types.index') }}"
                :presenter="\App\Presenters\DamageTypePresenter::dataTableLayout()"
                export_filename="export-damage-types-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
