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
    Datos Laborales
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollEmploymentForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar los Datos Laborales
                        @include('buttons.help', [
                            'helpId' => 'PayrollEmploymentForm',
                            'helpSteps' => get_json_resource('ui-guides/proceedings/employment_form.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <payroll-employment
                    route_list="{{ url('payroll/employments') }}"
                    :payroll_employment_id="{!! (isset($payrollEmployment)) ? $payrollEmployment->id : "null" !!}"
                >
                </payroll-employment>
            </div>
        </div>
    </div>
@stop
