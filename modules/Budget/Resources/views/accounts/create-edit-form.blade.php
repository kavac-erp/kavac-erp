@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    {{ __('Presupuesto') }}
@stop

@section('maproute-title')
    {{ __('Catálogo de Cuentas') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Cuenta Presupuestaria') }}
                        @include('buttons.help', [
                            'helpId' => 'budgetAccountsForm',
                            'helpSteps' => get_json_resource('ui-guides/budget_accounts.json', 'budget')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! (!isset($model)) ? Form::open($header) : Form::model($model, $header) !!}
                    <div id="budgetAccountsForm" class="card-body">
                        @include('layouts.form-errors')
                        <div class="row" id="help_account">
                            <div id="help_account_id" class="col-lg-6">
                                <div class="form-group is-required">
                                    <label for="" class="control-label">{{ __('Cuenta') }}</label>
                                    {!! Form::select('parent_id', $budget_accounts, null, [
                                        'id' => 'parent_id',
                                        'class' => 'select2',
                                        'data-toggle' => 'tooltip',
                                        'title' => __('Seleccione una cuenta presupuestaria')
                                    ]) !!}
                                </div>
                            </div>
                            <div id="help_code_id" class="col-lg-6">
                                <div class="form-group is-required">
                                    <label for="code_id" class="control-label">{{ __('Código') }}</label>
                                    {!! Form::text('code', old('code'), [
                                        'id' => 'code_id',
                                        'class' => 'form-control input-sm',
                                        'data-inputmask' => "'mask': '9.99.99.99.99'",
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => '0.00.00.00.00',
                                        'title' => __('Código de la cuenta presupuestaria')
                                    ]) !!}
                                </div>
                            </div>
                            <div id="help_denomination_id" class="col-lg-6">
                                <div class="form-group is-required">
                                    <label for="" class="control-label">{{ __('Denominación') }}</label>
                                    {!! Form::text('denomination', old('denomination'), [
                                        'class' => 'form-control input-sm',
                                        'placeholder' => __('descripción de la cuenta'),
                                        'title' => __('Denominacón o concepto de la cuenta'),
                                        'data-toggle' => 'tooltip'
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div id="help_parameters_id" class="row">
                                    <div class="col-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="resource" class="control-label">{{ __('Recurso') }}</label>
                                            <div class="custom-control custom-switch">
                                                {!! Form::radio(
                                                    'account_type',
                                                    'resource',
                                                    (isset($model) && $model->resource), [
                                                        'id' => 'resource',
                                                        'class' => 'custom-control-input'
                                                    ]
                                                ) !!}
                                                <label class="custom-control-label" for="resource"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="egress" class="control-label">{{ __('Egreso') }}</label>
                                            <div class="custom-control custom-switch">
                                                {!! Form::radio(
                                                    'account_type', 'egress',
                                                    (isset($model) && $model->egress), [
                                                        'id' => 'egress',
                                                        'class' => 'custom-control-input'
                                                    ]
                                                ) !!}
                                                <label class="custom-control-label" for="egress"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="" class="control-label">{{ __('Original') }}</label>
                                            <div class="custom-control custom-switch">
                                                {!! Form::checkbox(
                                                    'original',
                                                    true, (isset($model) && $model->original), [
                                                        'id' => 'original',
                                                        'class' => 'custom-control-input'
                                                    ]
                                                ) !!}
                                                <label class="custom-control-label" for="original"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="" class="control-label">{{ __('Activa') }}</label>
                                            <div class="custom-control custom-switch">
                                                {!! Form::checkbox(
                                                    'active',
                                                    true,
                                                    (isset($model) && $model->active), [
                                                        'id' => 'active',
                                                        'class' => 'custom-control-input'
                                                    ]
                                                ) !!}
                                                <label class="custom-control-label" for="active"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="disaggregate_tax" class="control-label">{{ __('Afecta Impuesto') }}</label>
                                            <div class="custom-control custom-switch">
                                                {!! Form::checkbox(
                                                    'disaggregate_tax',
                                                    true,
                                                    (isset($model) && $model->disaggregate_tax), [
                                                        'id' => 'disaggregate_tax',
                                                        'class' => 'custom-control-input'
                                                    ]
                                                ) !!}
                                                <label class="custom-control-label" for="disaggregate_tax"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        @include('layouts.form-buttons')
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    <script>
        $(document).ready(function() {
            /** Genera una nueva cuenta a partir de la cuenta seleccionada */
            $("#parent_id").on('change', function() {
                $("input[type=text]").each(function() {
                    $(this).val("");
                });

                if ($(this).val()) {
                    axios.get(window.app_url + '/budget/set-children-account/' + $(this).val()).then(response => {
                        if (response.data.result) {
                            let new_account = response.data.new_account;
                            /** Genera las nuevas cuentas */
                            $("input[name=code]").val(
                                `${new_account.group}
                                 ${new_account.item}
                                 ${new_account.generic}
                                 ${new_account.specific}
                                 ${new_account.subspecific}`
                            );
                            $("input[name=denomination]").val(new_account.denomination);
                            $('input[value=egress]').prop('checked', new_account.egress);
                            $('input[value=resource]').prop('checked', new_account.resource);
                        }
                    }).catch(error => {
                        log('budget::accounts.create-edit-form', 181, error);
                    });
                }
            });

            // Eventos al hacer clic en el botón de reset
            $(".btn.btn-default.btn-icon.btn-round").on('click', function() {
                $("#parent_id").val('').trigger('change');
            });
        });
    </script>
@stop
