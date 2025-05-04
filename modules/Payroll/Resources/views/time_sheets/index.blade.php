@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion ion-ios-calendar-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion ion-ios-calendar-outline"></i>
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
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Hoja de tiempo</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.time-sheet.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-time-sheet-list
                        route_delete="{{ url('payroll/time-sheet') }}"
                        route_edit="{{ url('payroll/time-sheet/{id}/edit') }}"
                        route_show="{{ url('payroll/time-sheet/vue-info/{id}') }}"
                        route_list="{{ url('payroll/time-sheet/vue-list') }}"
                        approve_permission="{{ auth()->user()->hasPermission('payroll.timesheet.approve') }}"
                        reject_permission="{{ auth()->user()->hasPermission('payroll.timesheet.reject') }}"
                        confirm_permission="{{ auth()->user()->hasPermission('payroll.timesheet.confirm') }}"
                    >
                    </payroll-time-sheet-list>
                </div>
            </div>
        </div>
    </div>
@stop