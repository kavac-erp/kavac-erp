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
	Datos Profesionales
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card" id="cardPayrollProfessionalForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar los Datos Profesionales
                        @include('buttons.help', [
                            'helpId' => 'PayroStaffForm',
                            'helpSteps' => get_json_resource('ui-guides/proceedings/professional_form.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
				<payroll-professional
					:payroll_professional_id="{!! (isset($payrollProfessional)) ? $payrollProfessional->id : "null" !!}"
                    route_list="{{ url('payroll/professionals') }}">
                </payroll-professional>
            </div>
		</div>
	</div>
@stop
