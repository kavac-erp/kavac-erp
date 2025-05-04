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
    Órdenes de compras / servicios
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Listado de órdenes de compras / servicios</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('purchase.direct_hire.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <purchase-order-direct-hire-list
                        :records="{{ $records }}"
                        route_edit="{{ url('purchase/direct_hire/{id}/edit') }}"
                        route_delete="/purchase/direct_hire"
                    />
                </div>
            </div>
        </div>
    </div>
@stop
