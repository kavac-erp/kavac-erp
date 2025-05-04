@extends('layouts.app')

@section('maproute-icon')
	<i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
	<i class="ion-settings"></i>
@stop

@section('maproute-actual')
	{{ __('Usuario') }}
@stop

@section('maproute-title')
	{{ __('Permisos de Acceso') }}
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h6 class="card-title">{{ __('Usuario (:user)', ['user' => $user->name]) }} - {{ __('Roles y Permisos') }}</h6>
					<div class="card-btns">
						@include('buttons.previous', ['route' => url()->previous()])
						@include('buttons.minimize')
					</div>
				</div>
					<user-roles-permissions 
						:user='{!! json_encode($user) !!}' 
						:user-roles='{!! json_encode($user->roles()->get()) !!}' 
						:user-permissions='{!! json_encode($user->permissions()->get()) !!}'
						:roles='{!! json_encode(App\Roles\Models\Role::with("permissions")->get()) !!}' 
						:permissions='{!! json_encode(App\Roles\Models\Permission::all()) !!}'
					/>
			</div>
		</div>
	</div>
@stop
