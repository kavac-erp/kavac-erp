@extends('layouts.app')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Cierre de ejercicio') }}
@stop

@section('maproute-title')
    {{ __('Configuración') }}
@stop

@section('content')
    <!-- Formatos de códigos -->
    @permission('closefiscalyear.setting')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">
                            {{ __('Formatos de Códigos') }}
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    {!! Form::open(['id' => 'form-codes', 'route' => 'close-fiscal-year.settings.store', 'method' => 'post']) !!}
                        {!! Form::token() !!}
                        <div class="card-body" id="helpCodeSetting">
                            @include('layouts.help-text', ['codeSetting' => true])
                            @include('layouts.form-errors')
                            <div class="row">
                                <div class="col-12">
                                    <h6>{{ __('Asientos contables de ajustes') }}</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4" id="helpCode">
                                    <div class="form-group">
                                        {!! Form::label('entries_reference', __('Código de referencia'), []) !!}
                                        {!! Form::text('entries_reference', ($code) ? $code->format_code : old('entries_reference'), [
                                            'class' => 'form-control input-sm', 'data-toggle' => 'tooltip',
                                            'title' => __('Formato para el código para la referencia del asiento contable'),
                                            'placeholder' => 'Ej. XXX-00000000-YYYY',
                                            'readonly' => ($code) ? true : false
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                        @if (!$code)
                            <div class="card-footer text-right">
                                @include('layouts.form-buttons')
                            </div>
                        @endif
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    @endpermission
    <!-- Final Formatos de códigos -->
    <!-- Componente para registrar la cuenta a utilizar para el cierre de ejercicio -->
    <close-fiscal-years-account></close-fiscal-years-account>
@stop