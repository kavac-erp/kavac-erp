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
    Gestión de registro de planilla ARI
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <payroll-ari-register-form route_list="{{ url('payroll/ari-register') }}"
                :ari_register="{{ $payrollAriRegister ?? json_encode('') }}">
            </payroll-ari-register-form>
        </div>
    </div>
@stop
