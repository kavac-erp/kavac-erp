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
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Personal en Disfrute de Vacaciones</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-report-staff-vacation-enjoyment>
                    </payroll-report-staff-vacation-enjoyment>
                </div>
            </div>
        </div>
    </div>
@stop
