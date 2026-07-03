@extends('layouts/default')

@section('title')
    @if ($item->id)
        {{ trans('admin/damages/general.update_type') }}
    @else
        {{ trans('admin/damages/general.add_type') }}
    @endif
    @parent
@stop

@section('header_right')
    <a href="{{ route('damage-types.index') }}" class="btn btn-primary pull-right">{{ trans('general.back') }}</a>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        @if ($item->id)
            <form class="form-horizontal" method="post" action="{{ route('damage-types.update', $item->id) }}">
                {{ method_field('PUT') }}
        @else
            <form class="form-horizontal" method="post" action="{{ route('damage-types.store') }}">
        @endif
        {{ csrf_field() }}

        <div class="box box-default">
            <div class="box-body">
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <label for="name" class="col-md-3 control-label">{{ trans('general.name') }}</label>
                    <div class="col-md-7">
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                    </div>
                    {!! $errors->first('name', '<div class="col-md-2"><span class="alert-msg">:message</span></div>') !!}
                </div>

                <div class="form-group {{ $errors->has('default_cost') ? 'has-error' : '' }}">
                    <label for="default_cost" class="col-md-3 control-label">{{ trans('admin/damages/general.default_cost') }}</label>
                    <div class="col-md-4">
                        <input type="text" name="default_cost" id="default_cost" class="form-control" value="{{ old('default_cost', $item->default_cost) }}">
                    </div>
                    {!! $errors->first('default_cost', '<div class="col-md-5"><span class="alert-msg">:message</span></div>') !!}
                </div>

                <div class="form-group">
                    <label for="is_critical" class="col-md-3 control-label">{{ trans('admin/damages/general.critical_component') }}</label>
                    <div class="col-md-7">
                        <label class="checkbox-inline" style="padding-top:6px;">
                            <input type="checkbox" name="is_critical" id="is_critical" value="1" {{ old('is_critical', $item->is_critical) ? 'checked' : '' }}>
                            {{ trans('admin/damages/general.critical_component_hint') }}
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category_id" class="col-md-3 control-label">{{ trans('general.category') }}</label>
                    <div class="col-md-7">
                        <select name="category_id" id="category_id" class="form-control select2">
                            <option value="">{{ trans('admin/damages/general.all_categories') }}</option>
                            @foreach (\App\Models\Category::where('category_type', 'asset')->orderBy('name')->get() as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="col-md-3 control-label">{{ trans('general.notes') }}</label>
                    <div class="col-md-7">
                        <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $item->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <button type="submit" class="btn btn-primary">{{ trans('general.save') }}</button>
            </div>
        </div>
        </form>
    </div>
</div>
@stop
