@extends('sale::layouts.master')

@section('maproute-icon')
	<i class="ion-ios-list-outline"></i>
@stop

@section('maproute-icon-mini')
	<i class="ion-ios-list-outline"></i>
@stop

@section('maproute-actual')
	Comercialización
@stop

@section('maproute-title')
	Ingresos de almacén
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">Ingresos de Almacén</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
						@include('buttons.new', ['route' => route('sale.reception.create')])
						@include('buttons.minimize')
					</div>
				</div>
				<div class="card-body">
					<sale-warehouse-reception-list
						route_list="{{ url('sale/receptions/vue-list') }}"
						route_edit="{{ url('sale/receptions/edit/{id}') }}"
						route_delete="{{ url('sale/receptions/delete') }}"
						:has_role="{!! (isset($hasRole)) ? $hasRole : 'null' !!}"
						route_update="sale/receptions">
					</sale-warehouse-reception-list>
				</div>
			</div>
		</div>
	</div>
@stop
