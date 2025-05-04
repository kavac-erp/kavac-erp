@extends('accounting::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    Contabilidad
@stop

@section('maproute-title')
    Configuración
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpCodeSettingForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Formatos de Códigos') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    @include('layouts.help-text', ['codeSetting' => true])
                    <div class="row">
                        <div class="col-12">
                            @if (!is_null($refCode))
                                <accounting-setting-code :ref_code="{{ $refCode }}"
                                    route_list="{{ route('accounting.settings.index') }}" />
                            @else
                                <accounting-setting-code route_list="{{ route('accounting.settings.index') }}" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card" id="accounting_institution_account">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Configuración de la Cuenta contable de la institución') }}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! Form::open(['route' => 'accounting.parameters.update-institution-parameters', 'method' => 'post']) !!}
                {!! Form::token() !!}
                <div class="card-body">
                    @include('layouts.form-errors')
                    <div class="row">
                        <div class="col-md-4" id="helpWorkAge">
                            <div class="form-group">
                                {!! Form::label('p_value', 'Cuenta contable de la institución', []) !!}
                                {!! Form::select(
                                    'p_value',
                                    isset($accounting_accounts) ? $accounting_accounts : [],
                                    $parameter ? $parameter->p_value : old('p_value'),
                                    [
                                        'class' => 'form-control select2',
                                        'id' => 'city_id',
                                    ],
                                ) !!}
                                {!! Form::hidden('p_key', 'institution_account') !!}
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
        <div class="col-12">
            <div class="card" id="helpSettingForm">
                <div class="card-header">
                    <h6 class="card-title">
                        Registros comunes
                        @include('buttons.help', [
                            'helpId' => 'AccountingSetting',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_setting.json',
                                'accounting'),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div id="helpSettingCategory">
                            <accounting-setting-category></accounting-setting-category>
                        </div>
                        <div id="helpSettingAccount">
                            <accounting-setting-account
                                route_export="{{ route('accounting.accounts.export.all') }}"></accounting-setting-account>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('extra-js')
    <script type="text/javascript">
        let selectElement = document.querySelector('select[name="p_value"]');
        let selectedValue = selectElement.value;
        let options = selectElement.options;

        for (let i = 0; i < options.length; i++) {
            let institutional = options[i].text.split('.')[6];

            if (institutional) {
                if (institutional.split('-')[0] == 000) {
                    options[i].disabled = true;
                }
            }
        }
    </script>
@stop
