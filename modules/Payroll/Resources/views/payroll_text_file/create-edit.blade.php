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
            <payroll-text-file :payroll_text_file_id="{!! isset($payrollTextFile) ? $payrollTextFile->id : 'null' !!}"
                route_list="{{ url('payroll/file') }}"></payroll-text-file>
        </div>
    </div>
@stop
