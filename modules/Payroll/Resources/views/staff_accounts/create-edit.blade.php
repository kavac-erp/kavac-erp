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
    Datos contables
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollStaffAccountForm">
                <div class="card-header">
                    <h6 class="card-title">{{(isset($id)) ? 'Actualizar los Datos contables' : 'Registrar los Datos contables'}}
                        @include('buttons.help', [
                            'helpId' => 'PayrollStaffAccountForm',
                            'helpSteps' => get_json_resource('ui-guides/proceedings/employment_form.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <payroll-staff-account-form
                    route_list="{{ url('payroll/staff-accounts') }}"
                    :payroll_staff_account_id="{!! (isset($id)) ? $id : "null" !!}"
                >
                </payroll-staff-account-form>
            </div>
        </div>
    </div>
@stop
