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
    Requerimientos
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Listado de Requerimientos</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @if($codeAvailable)
                            @include('buttons.new', ['route' => route('purchase.requirements.create')])
                        @endif
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    @if(!$codeAvailable)
                        <div class="alert alert-danger" role="alert">
                            <div class="container">
                                <div class="alert-icon">
                                    <i class="now-ui-icons objects_support-17"></i>
                                </div>
                                <strong>¡Atención!</strong> Debe verificar los siguientes errores antes de continuar:
                                <ul>
                                    <li>Configurar el formato de código para el requerimiento en configuración de Compras</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    <purchase-requirements
                        route_edit="{{ url('purchase/requirements/{id}/edit') }}"
                    />
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Listado de Presupuesto base</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        {{-- @include('buttons.new', ['route' => route('purchase.base_budget.create')]) --}}
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <purchase-base-budget
                        :employments ="{{$employments}}"
                        :employments_users="{{$employments_users}}"
                        :has_budget="{{ isset($has_budget)?'true':'false' }}"
                        route_edit="{{ url('purchase/base_budget/{id}/edit') }}"
                        :has_availability_request_permission="{{ $has_availability_request_permission }}"
                    />
                </div>
            </div>
        </div>
    </div>
@stop
