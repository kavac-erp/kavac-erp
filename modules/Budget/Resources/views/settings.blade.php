@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Presupuesto') }}
@stop

@section('maproute-title')
    {{ __('Configuración') }}
@stop

@section('content')
    <!-- Formatos de códigos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Formatos de Códigos') }}
                        {{--
                        // Issue #96: Solicitaron que no se muestre el botón de ayuda en esta sección
                        @include('buttons.help', [
                            'helpId' => 'BudgetCodeSetting',
                            'helpSteps' => get_json_resource('ui-guides/budget_code_settings.json', 'budget')
                        ])
                    --}}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! Form::open(['id' => 'form-codes', 'route' => 'budget.settings.store', 'method' => 'post']) !!}
                {!! Form::token() !!}
                <div class="card-body" id="helpCodeSetting">
                    @include('layouts.help-text', ['codeSetting' => true])
                    @include('layouts.form-errors')
                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Formulación') }}</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" id="helpCodeFormulations">
                            <div class="form-group">
                                {!! Form::label('formulations_code', __('Código de Formulación'), []) !!}
                                {!! Form::text('formulations_code', $fCode ? $fCode->format_code : old('formulations_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código de la formulación de presupuesto'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $fCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Ejecución</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" id="helpCodeCompromises">
                            <div class="form-group">
                                {!! Form::label('compromises_code', __('Código de Compromiso'), []) !!}
                                {!! Form::text('compromises_code', $cCode ? $cCode->format_code : old('compromises_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código del compromiso'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $cCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeCaused">
                            <div class="form-group">
                                {!! Form::label('caused_code', __('Código de Causado'), []) !!}
                                {!! Form::text('caused_code', $caCode ? $caCode->format_code : old('caused_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código del causado'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $caCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodePayed">
                            <div class="form-group">
                                {!! Form::label('payed_code', __('Código de Pagado'), []) !!}
                                {!! Form::text('payed_code', $pCode ? $pCode->format_code : old('payed_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código del pagado'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $pCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeTransfers">
                            <div class="form-group">
                                {!! Form::label('transfers_code', __('Código de Traspaso'), []) !!}
                                {!! Form::text('transfers_code', $tCode ? $tCode->format_code : old('transfers_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código del traspaso'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $tCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeReductions">
                            <div class="form-group">
                                {!! Form::label('reductions_code', __('Código de Reducción'), []) !!}
                                {!! Form::text('reductions_code', $rCode ? $rCode->format_code : old('reductions_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código de reducciones'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $rCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeCredits">
                            <div class="form-group">
                                {!! Form::label('credits_code', __('Código de Crédito Adicional'), []) !!}
                                {!! Form::text('credits_code', $crCode ? $crCode->format_code : old('credits_code'), [
                                    'class' => 'form-control input-sm',
                                    'data-toggle' => 'tooltip',
                                    'title' => __('Formato para el código de créditos adicionales'),
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $crCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeCredits">
                            <div class="form-group">
                                {!! Form::label('budgetary_availabilities_code', __('Código de la Disponibilidad Presupuestaria manual'), []) !!}
                                {!! Form::text(
                                    'budgetary_availabilities_code',
                                    $bamCode ? $bamCode->format_code : old('budgetary_availabilities_code'),
                                    [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'title' => __('Formato para el código de la disponibilidad presupuestaria manual'),
                                        'placeholder' => 'Ej. XXX-00000000-YYYY',
                                        'readonly' => $bamCode ? true : false,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @if (!$fCode || !$cCode || !$caCode || !$pCode || !$tCode || !$rCode || !$crCode || !$bamCode)
                    <div class="card-footer text-right">
                        @include('layouts.form-buttons')
                    </div>
                @endif
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <!-- Final Formatos de códigos -->

    <!-- Registros comunes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Registros comunes</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Configuración de las Fuentes de Financiamiento --}}
                        <budget-financement-types></budget-financement-types>
                        {{-- Configuración de los Tipos de Financiamiento --}}
                        <budget-financement-sources></budget-financement-sources>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Final Registros comunes -->

    <!-- Proyectos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Proyectos') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('budget.projects.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <budget-projectlist route_list="{{ url('budget/projects/vue-list') }}"
                        route_delete="{{ url('budget/projects') }}" route_edit="{{ url('budget/projects/{id}/edit') }}"
                        onapre="{{ config('institution.use_onapre') ? 'true' : 'false' }}">
                    </budget-projectlist>
                </div>
            </div>
        </div>
    </div>
    <!-- Final Proyectos -->

    <!-- Acciones centralizadas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Acciones Centralizadas') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('budget.centralized-actions.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <budget-centralized-actions-list route_list="{{ url('budget/centralized-actions/vue-list') }}"
                        route_delete="{{ url('budget/centralized-actions') }}"
                        route_edit="{{ url('budget/centralized-actions/{id}/edit') }}">
                    </budget-centralized-actions-list>
                </div>
            </div>
        </div>
    </div>
    <!-- Final Acciones centralizadas -->

    <!-- Acciones específicas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Acciones Específicas') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('budget.specific-actions.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body" id="helpBudgetSpecificActionsList">
                    <budget-actsplist route_list="{{ url('budget/specific-actions/vue-list') }}"
                        route_delete="{{ url('budget/specific-actions') }}"
                        route_edit="{{ url('budget/specific-actions/{id}/edit') }}">
                    </budget-actsplist>
                </div>
            </div>
        </div>
    </div>
    <!-- Final Acciones específicas -->
@stop
