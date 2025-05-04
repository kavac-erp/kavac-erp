@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Gestión de tablas salariales
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Ajustes en tablas salariales</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.salary-adjustments.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-12">
                        <payroll-salary-adjustments-list route_list="{{ url('payroll/salary-adjustments/vue-list') }}"
                                              route_delete="{{ url('payroll/salary-adjustments') }}"
                                              route_edit="{{ url('payroll/salary-adjustments/{id}/edit') }}"
                                              route_show="{{ url('payroll/salary-adjustments/{id}') }}">
                        </payroll-salary-adjustments-list>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop