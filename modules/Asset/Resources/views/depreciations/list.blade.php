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
	Gestión de Bienes
@stop

@section('content')
	@permission('asset.depreciation.list')
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h6 class="card-title">Depreciación anual de bienes</h6>
					</div>
					<div class="card-body">

						<asset-depreciation-list
							route_list="{{ url('asset/depreciations/vue-list') }}"
						>
						</asset-depreciation-list>
					</div>
				</div>
			</div>
		</div>
	@endpermission
@stop