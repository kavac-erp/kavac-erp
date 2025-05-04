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
    Esquema de Guardias
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollGuardSchemeForm">
                <div class="card-header">
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                    <h6 class="card-title">Registrar Esquema de Guardias</h6>
                </div>
                <payroll-guard-scheme-form
                    :id="{!! (isset($guardScheme)) ? $guardScheme->id : 'null' !!}"
                    route_list="{{ url('payroll/guard-schemes') }}">
                </payroll-guard-scheme-form>
            </div>
        </div>
    </div>
@stop
