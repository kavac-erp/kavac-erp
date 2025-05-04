@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Adelanto de prestaciones
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Solicitudes de adelanto de prestaciones</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.benefits-requests.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-benefits-request-list
                        route_list="{{ url('payroll/benefits-requests/vue-list') }}"
                        route_show="{{ url('payroll/benefits-requests/show/{id}') }}"
                        route_edit="{{ url('payroll/benefits-requests/edit/{id}') }}"
                        route_delete="{{ url('payroll/benefits-requests') }}"
                        route_update="{{ url('payroll/benefits-requests') }}">
                    </payroll-benefits-request-list>
                </div>
            </div>
        </div>
    </div>

@stop
