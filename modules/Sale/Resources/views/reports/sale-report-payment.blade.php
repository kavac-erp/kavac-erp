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
    Reporte de Pagos
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card" id="cardSaleReportForm">
            <div class="card-header">
                <h6 class="card-title text-uppercase">Reporte de Pagos Registrados
                </h6>
                <div class="card-btns">
                    @include('buttons.previous', ['route' => url()->previous()])
                    @include('buttons.minimize')
                </div>
            </div>
            <sale-report-payment
                route_list="{{ url()->previous() }}">
            </sale-report-payment>
        </div>
    </div>
</div>
@stop
