@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    {{ __('Presupuesto') }}
@stop

@section('maproute-title')
    {{ __('Formulaciones') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <budget-formulation-subspecific
				:formulation-id="{!! (isset($formulation)) ? $formulation->id : "null" !!}"
                route_list="{{ url('budget/subspecific-formulations') }}"
                :institutions="{{ $institutions }}"
                help-file="{{ json_encode(get_json_resource('ui-guides/budget_subspecific_formulation.json', 'budget')) }}"
			>
			</budget-formulation-subspecific>
        </div>
    </div>
@stop
