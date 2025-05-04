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
    Gestión de archivo de nómina
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Generar Archivo txt de Nómina </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <payroll-text-file :payroll_text_file_id="{!! isset($payrollTextFile) ? $payrollTextFile->id : 'null' !!}"
                    route_list="{{ url('payroll/file') }}"></payroll-text-file>
            </div>
        </div>
    </div>
@stop
