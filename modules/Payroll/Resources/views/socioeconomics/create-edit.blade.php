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
	Datos Socioeconómicos
@stop

@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card" id="cardPayrollSocioeconomicForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar los Datos Socioeconómicos
                        @include('buttons.help', [
                            'helpId' => 'PayrollStaffForm',
                            'helpSteps' => get_json_resource('ui-guides/proceedings/socioeconomic_form.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
				<payroll-socioeconomic
					:payroll_socioeconomic_id="{!! (isset($payrollSocioeconomic)) ? $payrollSocioeconomic->id : "null" !!}"
                    route_list="{{ url('payroll/socioeconomics') }}">
                </payroll-socioeconomic>
            </div>
        </div>
	</div>
@stop
