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
    Hoja de tiempo
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollTimeSheetForm">
                <div class="card-header">
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                    <h6 class="card-title">Registrar hoja de tiempo</h6>
                </div>
                <payroll-time-sheet-form
                    :payroll_time_sheet_id="{!! (isset($payrollTimeSheet)) ? $payrollTimeSheet->id : 'null' !!}"
                    route_list="{{ url('payroll/time-sheet') }}">
                </payroll-time-sheet-form>
            </div>
        </div>
    </div>
@stop
