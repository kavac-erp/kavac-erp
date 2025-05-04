@extends('layouts.app')

@section('maproute-icon')
	<i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
	<i class="ion-settings"></i>
@stop

@section('maproute-actual')
	{{ __('Configuración') }}
@stop

@section('maproute-title')
	{{ __('Acceso al Sistema') }}
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">{{ __('Roles y Permisos') }}</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
						@include('buttons.minimize')
					</div>
				</div>
				@php
					$roles = App\Roles\Models\Role::with('permissions')->get();
					$permissions = App\Roles\Models\Permission::with('roles')->orderBy('model_prefix')->get();
				@endphp
				<roles-permissions
					roles-permissions-url='{!! route('get.roles.permissions') !!}'
					save-url='auth/settings/roles-permissions'
				></roles-permissions>
			</div>
		</div>
	</div>
@stop

@section('extra-css')
	@parent
	<style>
		.table-roles-permissions {
			font-size: .578rem
		}
		.table-roles-permissions .pretty {
			margin: 0 auto;
			font-weight: bold;
			font-size: .678rem
		}
		.table-roles-permissions .pretty .state label {
			text-indent: 0;
			min-width: 0;
		}
	</style>
@stop
