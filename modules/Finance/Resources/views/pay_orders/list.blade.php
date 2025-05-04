@extends('finance::layouts.master')

@section('maproute-icon')
	<i class="mdi mdi-ballot-outline"></i>
@stop

@section('maproute-icon-mini')
	<i class="mdi mdi-ballot-outline"></i>
@stop

@section('maproute-actual')
	{{ __('Finanzas') }}
@stop

@section('maproute-title')
	{{ __('Órdenes de Pago') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Listado de Órdenes de Pago') }}
                        {{-- @include('buttons.help') --}}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('finance.pay-orders.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <finance-pay-order-list
                        route_delete="{{ url('finance/pay-orders') }}"
                        route_edit="{{ url('finance/pay-orders/{id}/edit') }}"
                    />
                </div>
            </div>
        </div>
    </div>
@endsection