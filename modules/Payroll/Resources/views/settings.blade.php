@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Talento Humano') }}
@stop

@section('maproute-title')
    {{ __('Configuración') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="codeSettingForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Formatos de Códigos') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! Form::open(['id' => 'form-codes', 'route' => 'payroll.settings.store', 'method' => 'post']) !!}
                {!! Form::token() !!}
                <div class="card-body">
                    @include('layouts.help-text', ['codeSetting' => true])
                    @if($errors->any() && !$errors->has('p_value'))
                        @include('layouts.form-errors')
                    @endif
                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Personal') }}</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" id="staffsCode">
                            <div class="form-group">
                                {!! Form::label('staffs_code', 'Código del personal', []) !!}
                                {!! Form::text('staffs_code', ($sCode) ? $sCode->format_code : old('staffs_code'), [
                                'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                'title' => 'Formato para el código del personal',
                                'placeholder' => 'Ej. XXX-00000000-YYYY',
                                'readonly' => ($sCode) ? true : false
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="vacationRequestsCode">
                            <div class="form-group">
                                {!! Form::label('vacation_requests_code', 'Código de las solicitudes de vacaciones', []) !!}
                                {!! Form::text('vacation_requests_code', ($vRCode) ? $vRCode->format_code : old('vacation_requests_code'), [
                                'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                'title' => 'Formato para el código de las solicitudes de vacaciones',
                                'placeholder' => 'Ej. XXX-00000000-YYYY',
                                'readonly' => ($vRCode) ? true : false
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="benefitsRequestsCode">
                            <div class="form-group">
                                {!! Form::label('benefits_requests_code', 'Código de las solicitudes de adelanto de prestaciones', []) !!}
                                {!! Form::text('benefits_requests_code', ($bRCode) ? $bRCode->format_code : old('benefits_requests_code'), [
                                'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                'title' => 'Formato para el código de las solicitudes de adelanto de prestaciones',
                                'placeholder' => 'Ej. XXX-00000000-YYYY',
                                'readonly' => ($bRCode) ? true : false
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Nómina') }}</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" id="payrollsCode">
                            <div class="form-group">
                                {!! Form::label('payrolls_code','Código de la nómina',[]) !!}
                                {!! Form::text('payrolls_code', ($pCode)
                                ? $pCode->format_code
                                : old('payrolls_code'), [
                                'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                'title' => 'Formato para el código de la nómina',
                                'placeholder' => 'Ej. XXX-00000000-YYYY',
                                'readonly' => ($pCode) ? true : false
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="salaryScalesCode">
                            <div class="form-group">
                                {!! Form::label('salary_scales_code','Código de los escalafones salariales',[]) !!}
                                {!! Form::text('salary_scales_code', ($ssCode)
                                ? $ssCode->format_code
                                : old('salary_scales_code'), [
                                'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                'title' => 'Formato para el código de los escalafones salariales',
                                'placeholder' => 'Ej. XXX-00000000-YYYY',
                                'readonly' => ($ssCode) ? true : false
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="salaryTabulatorsCode">
                            <div class="form-group">
                                {!! Form::label('salary_tabulators_code','Código de los tabuladores salariales',[]) !!}
                                {!! Form::text('salary_tabulators_code', ($stCode)
                                ? $stCode->format_code
                                : old('salary_tabulators_code'), [
                                'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                'title' => 'Formato para el código de los tabuladores salariales',
                                'placeholder' => 'Ej. XXX-00000000-YYYY',
                                'readonly' => ($stCode) ? true : false
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @if (!$sCode || !$vRCode || !$bRCode || !$ssCode || !$stCode || !$pCode)
                    <div class="card-footer text-right">
                        @include('layouts.form-buttons')
                    </div>
                @endif
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card" id="payroll_work_age">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Configuración de la Edad Laboral Permitida') }}
                        @include('buttons.help', [
                            'helpId' => 'PayrollWorkAge',
                            'helpSteps' => get_json_resource('ui-guides/settings/work_age.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! Form::open(['route' => 'payroll.parameters.update-report-parameters', 'method' => 'post']) !!}
                    {!! Form::token() !!}
                    <div class="card-body" style="min-height: 100px">
                        @if($errors->any() && $errors->has('p_value') && preg_match('/^(\w+) edad laboral (\w+)/i', $errors->first('p_value')))
                            @include('layouts.form-errors')
                        @endif
                        <div class="row">
                            <div class="col-md-4" id="helpWorkAge">
                                <div class="form-group">
                                    @if (Modules\Payroll\Models\Parameter::where([
                                        'active' => true, 'required_by' => 'payroll',
                                        'p_key' => 'work_age'
                                    ])->first())
                                        {!! Form::label('p_value', 'Edad', []) !!}
                                        {!! Form::number('p_value', ($parameter) ? $parameter->p_value : old('p_value'), [
                                            'class' => 'form-control', 'data-toggle' => 'tooltip',
                                            'title' => 'Indique la edad laboral permitida', 'min' => '1',
                                            'placeholder' => 'Edad'
                                        ]) !!}
                                        {!! Form::hidden('p_key', 'work_age') !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right" id="helpParamButtons">
                        @include('layouts.form-buttons')
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <!--Sección de Configuración de parametros para reporte de nómina -->
    <!-- Consulta de los parametros almacenados en el modelo Parameter -->
    @php
        $PayrollReportConfigurations = (Modules\Payroll\Models\Parameter::where(['active' => true, 'required_by' => 'payroll'])->orderBy('id')->get())
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpPayrollReportConfigurations">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Configuración de parametros para reporte de nómina') }}
                        @include('buttons.help', [
                            'helpId' => 'PayrollReportConfigurations',
                            'helpSteps' => get_json_resource('ui-guides/settings/report_configurations.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! Form::open(['id' => 'form-report-configurations', 'route' => 'payroll.parameters.update-report-parameters', 'method' => 'post']) !!}
                {!! Form::token() !!}
                <div class="card-body" style="min-height: 100px">
                    @if($errors->any() && $errors->has('p_value') && !preg_match('/^(\w+) edad laboral (\w+)/i', $errors->first('p_value')))
                        @include('layouts.form-errors')
                    @endif
                    <div class="row">
                        @if ($PayrollReportConfigurations)
                            @foreach($PayrollReportConfigurations as $PayrollReportConfiguration)
                                @if($PayrollReportConfiguration['p_key'] == 'number_decimals')
                                    <div class="col-md-4" id="helpNumberDecimals">
                                        <div class="form-group">
                                            {!!
                                            Form::label('number_decimals', 'Número de decimales', []) !!}
                                            {!!
                                            Form::text('number_decimals', ($PayrollReportConfiguration) ? $PayrollReportConfiguration['p_value'] : old('number_decimals'), [
                                            'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                            'title' => 'Indique el número de decimales',
                                            'placeholder' => 'Número de decimales',
                                            'data-inputmask' => "'mask': '9'"
                                            ])
                                            !!}
                                        </div>
                                    </div>
                                @elseif($PayrollReportConfiguration['p_key'] == 'round')
                                    <div class="col-md-4" id="helpRound">
                                        <div class="form-group">
                                            {!! Form::label('round', 'Redondear', []) !!}
                                            <div class=" custom-control custom-switch">
                                                {!! Form::checkbox('round', true, ( !is_null($PayrollReportConfiguration) && $PayrollReportConfiguration['p_value'] === 'true'), [ 'id' => 'round', 'class' => 'custom-control-input']) !!}
                                                <label class="custom-control-label" for="round"></label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="card-footer text-right" id="helpParamReport">
                    @include('layouts.form-buttons')
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card" id="payroll_common">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Registros Comunes') }}
                        @include('buttons.help', [
                            'helpId' => 'PayrollCommon',
                            'helpSteps' => get_json_resource('ui-guides/settings/common.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Tipos de personal --}}
                        <payroll-staff-types></payroll-staff-types>

                        {{-- Tipos de cargos --}}
                        <payroll-position-types></payroll-position-types>

                        {{-- Cargos --}}
                        <payroll-positions></payroll-positions>
                        <!--
                        {{-- Clasificaciones del personal --}}
                        <payroll-staff-classifications></payroll-staff-classifications>
                        -->
                        {{-- Grados de instrucción --}}
                        <payroll-instruction-degrees></payroll-instruction-degrees>

                        {{-- Tipos de estudio --}}
                        <payroll-study-types></payroll-study-types>

                        {{-- Nacionalidades --}}
                        <payroll-nationalities></payroll-nationalities>

                        {{-- Niveles de idioma --}}
                        <payroll-language-levels></payroll-language-levels>

                        {{-- Idiomas --}}
                        <payroll-languages></payroll-languages>

                        <!--
                        {{-- Géneros --}}
                        <payroll-genders></payroll-genders>
                        -->

                        {{-- Tipos de inactividad --}}
                        <payroll-inactivity-types></payroll-inactivity-types>

                        {{-- Tipos de contrato --}}
                        <payroll-contract-types></payroll-contract-types>

                        {{-- Tipos de sector --}}
                        <payroll-sector-types></payroll-sector-types>

                        {{-- Grados de licencia de conducir --}}
                        <payroll-license-degrees></payroll-license-degrees>

                        {{-- Tipos de sangre --}}
                        <payroll-blood-types></payroll-blood-types>

                        {{-- Parentescos --}}
                        <payroll-relationships></payroll-relationships>

                        {{-- Discapacidades --}}
                        <payroll-disabilities></payroll-disabilities>

                        {{-- Niveles de escolaridad --}}
                        <payroll-schooling-levels></payroll-schooling-levels>

                        {{-- Días feriados --}}
                        <payroll-holidays></payroll-holidays>

                        {{-- Tipos de beca --}}
                        <payroll-scholarship-types></payroll-scholarship-types>

                        {{-- Coordinaciones --}}
                        <payroll-coordinations></payroll-coordinations>

                        {{-- Nivel de Responsabilidades --}}
                        <payroll-responsibilities-level></payroll-responsibilities-level>

                        {{-- Responsables de ARC --}}
                        <payroll-arc-responsibles></payroll-arc-responsibles>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollGeneralParametersSettingForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Parámetros Generales de Nómina') }}
                        @include('buttons.help', [
                        'helpId' => 'GeneralParams',
                        'helpSteps' => get_json_resource('ui-guides/settings/general_parameters.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Tipo de excepción de jornada laboral --}}
                        <payroll-exception-types></payroll-exception-types>

                        {{-- Parámetros de nómina --}}
                        <payroll-parameters></payroll-parameters>

                        {{-- Escalafones salariales --}}
                        <payroll-salary-scales></payroll-salary-scales>

                        {{-- Tabuladores de nómina --}}
                        <payroll-salary-tabulators></payroll-salary-tabulators>

                        {{-- Tipos de conceptos --}}
                        <payroll-concept-types>
                        </payroll-concept-types>

                        {{-- Conceptos --}}
                        <payroll-concepts
                            route_list="{{ url('payroll/concepts') }}"
                            accounting="{{ Module::has('Accounting') && Module::isEnabled('Accounting') }}"
                            budget="{{ Module::has('Budget') && Module::isEnabled('Budget') }}">
                        </payroll-concepts>

                        {{-- Tipos de pago --}}
                        <payroll-payment-types
                            start_operations_date="{!! (isset($institution)) ? $institution->start_operations_date : '' !!}"
                            accounting="{{ Module::has('Accounting') && Module::isEnabled('Accounting') }}"
                            finance="{{ Module::has('Finance') && Module::isEnabled('Finance') }}"
                            :moment_close_permission="{{json_encode(auth()->user()->hasPermission('payroll.registers.moment.close'))}}">
                        </payroll-payment-types>

                        {{-- Políticas vacacionales --}}
                        <payroll-vacation-policies
                            start_operations_date="{!! (isset($institution)) ? $institution->start_operations_date : '' !!}">
                        </payroll-vacation-policies>

                        {{-- Políticas de prestaciones sociales --}}
                        <payroll-benefits-policies
                            start_operations_date="{!! (isset($institution)) ? $institution->start_operations_date : '' !!}">
                        </payroll-benefits-policies>

                        {{-- Políticas de Permisos --}}
                        <payroll-permission-policies></payroll-permission-policies>
                        <!--
                        {{-- Tipos de liquidación --}}
                        <payroll-settlement-types></payroll-settlement-types>
                        -->

                        {{-- Grupos de supervisados --}}
                        <payroll-supervised-groups></payroll-supervised-groups>

                        {{-- Parámetros de tiempo --}}
                        <payroll-time-sheet-parameters></payroll-time-sheet-parameters>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    {!! Html::script('js/ckeditor.js', [], Request::secure()) !!}
@stop
