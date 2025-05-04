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
    Reportes
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="overflow: visible">
                <div class="card-header">
                    <h6 class="card-title">Relación de conceptos</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-report-relationship-concepts route_list="{{ url('/payroll/reports/vue-list') }}">
                    </payroll-report-relationship-concepts>
                </div>
            </div>
        </div>
    </div>
@stop
