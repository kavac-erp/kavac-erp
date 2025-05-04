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
    Personal
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollStaffForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar los Datos Personales
                        @include('buttons.help', [
                            'helpId' => 'PayrollStaffForm',
                            'helpSteps' => get_json_resource('ui-guides/proceedings/staff_form.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <payroll-staff
                    :payroll_staff_id="{!! (isset($payrollStaff)) ? $payrollStaff->id : "null" !!}"
                    route_list="{{ url('payroll/staffs') }}"
                >
                </payroll-staff>
            </div>
        </div>
    </div>
@stop
