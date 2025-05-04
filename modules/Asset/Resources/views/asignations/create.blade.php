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
<div class="row">
	<div class="col-12">
		<div class="card" id="cardAssetAsignationForm">
			<div class="card-header">
				<h6 class="card-title text-uppercase">
					Asignación de Bienes
					@include('buttons.help', [
					    'helpId' => 'AssetAsignationForm',
					    'helpSteps' => get_json_resource('ui-guides/asignations/asignation_form.json', 'asset')
				    ])
				</h6>
				<div class="card-btns">
					@include('buttons.previous', ['route' => url()->previous()])
					@include('buttons.minimize')
				</div>
			</div>
			<asset-asignation-create route_list="{{ url('asset/asignations') }}"
									 route_asset="{{ url('asset/registers/vue-list/asignations') }}"
									 :asignationid="{!! (isset($asignation)) ? $asignation->id : 'null' !!}"
									 :assetid="{!! (isset($asset)) ? $asset['id'] : 'null' !!}"
									 :institution_id="{!! (isset($institution_id)) ? $institution_id : 'null' !!}"
									 :is_admin="{!! isset($is_admin) && $is_admin ? 'true' : 'false' !!}">
			</asset-asignation-create>
		</div>
	</div>
</div>
@stop
