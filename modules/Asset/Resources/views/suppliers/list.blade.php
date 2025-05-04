@extends('asset::layouts.master')

@section('maproute-icon')
	<i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-icon-mini')
	<i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-actual')
	Bienes
@stop

@section('maproute-title')
	Proveedores
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">Listado de Proveedores</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
						@include('buttons.new', ['route' => route('asset.suppliers.create')])
						@include('buttons.minimize')
					</div>
				</div>
				<div class="card-body">
					<asset-suppliers-list
						route_list='asset/suppliers/vue-list'
						route_delete="asset/suppliers"
						route_edit="/asset/suppliers/{id}/edit"
					/>
				</div>
			</div>
		</div>
	</div>
@stop
