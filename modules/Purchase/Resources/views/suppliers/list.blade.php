@extends('purchase::layouts.master')

@section('maproute-icon')
	<i class="ion-social-dropbox-outline"></i>
@stop

@section('maproute-icon-mini')
	<i class="ion-social-dropbox-outline"></i>
@stop

@section('maproute-actual')
	Compras
@stop

@section('maproute-title')
	Proveedores
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">{{ __('Listado de Proveedores') }}</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
						@include('buttons.new', ['route' => route('purchase.suppliers.create')])
						@include('buttons.minimize')
					</div>
				</div>
				<div class="card-body">
					<purchase-suppliers-list
						route_list='purchase/suppliers/vue-list'
						route_delete="purchase/suppliers"
						route_edit="/purchase/suppliers/{id}/edit"
					/>
				</div>
			</div>
		</div>
	</div>
@stop
