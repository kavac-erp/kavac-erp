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
	{{ __('Traspasos') }}
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">
						{{ __('Traspasos') }}
						@include('buttons.help', [
							'helpId' => 'BudgetAditionalCreditstHelp',
							'helpSteps' => get_json_resource('ui-guides/budget_modification.json', 'budget')
						])
					</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
						@include('buttons.minimize')
					</div>
				</div>
				<budgetmod
					type_modification="{!! $type !!}"
					edit_object="{{ (isset($model)) ? $model : '' }}"
					route_list="{{ route('budget.modifications.index') }}"
				/>
			</div>
		</div>
	</div>
@stop
