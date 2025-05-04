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
    Solicitud de pedidos
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Lista de pedidos</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('sale.order.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
			    <div class="card-body">
                  {{-- Lista de pedidos de cliente --}}
                  <register-order-list
                    route_list="{{ url('sale/order/vue-list') }}"
                    route_show="{{ url('sale/order/info/{id}') }}"
                    route_edit="{{ url('sale/order/edit/{id}') }}"
                    route_delete="{{ url('sale/order/delete') }}"
                    route_update="{{ url('sale/order') }}"
                    ></register-order-list>
                </div>
            </div>
        </div>
    </div>
@stop
