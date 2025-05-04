@extends('projecttracking::layouts.master', ['setting_view' => true])

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Seguimiento') }}
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
                            @include('buttons.help', [
                                'helpId' => 'ProjectTrackingCodeSetting',
                                'helpSteps' => get_json_resource('ui-guides/settings/code_setting.json', 'projecttracking')
                            ])
                        --}}
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! Form::open(['id' => 'form-codes', 'route' => 'projecttracking.settings.store', 'method' => 'post']) !!}
                {!! Form::token() !!}
                <div class="card-body" id="helpCodeSetting">
                    @include('layouts.help-text', ['codeSetting' => true])
                    @include('layouts.form-errors')
                    <div class="row">
                        <div class="col-12">
                            <h6> PROYECTOS / SUBPROYECTOS / PRODUCTOS</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" id="helpCodeProyect">
                            <div class="form-group">
                                {!! Form::label('projects_code', 'Código de los Proyectos', []) !!}
                                {!! Form::text('projects_code', $pjCode ? $pjCode->format_code : old('projects_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de los Proyectos',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $pjCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeSubProyect">
                            <div class="form-group">
                                {!! Form::label('sub_projects_code', 'Código de los Subproyectos', []) !!}
                                {!! Form::text('sub_projects_code', $spCode ? $spCode->format_code : old('sub_projects_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de los Subproyectos',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $spCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4" id="helpCodeProduct">
                            <div class="form-group">
                                {!! Form::label('products_code', 'Código de los Productos', []) !!}
                                {!! Form::text('products_code', $pdCode ? $pdCode->format_code : old('products_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de los Productos',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $pdCode ? true : false,
                                ]) !!}

                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>PLAN DE ACTIVIDADES</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" id="helpCodeActivityPlan">
                            <div class="form-group">
                                {!! Form::label('activity_plans_code', 'Código de los planes de actividad', []) !!}
                                {!! Form::text('activity_plans_code', $paCode ? $paCode->format_code : old('activity_plans_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de los planes de actividad',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $paCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @if (!$pjCode || !$spCode || !$pdCode || !$paCode)
                    <div class="card-footer text-right">
                        @include('layouts.form-buttons')
                    </div>
                @endif
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <!-- Final Formatos de códigos -->

    <div class="row">
        <div class="col-12">
            <div class="card" id="helpGeneralParamsForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('REGISTROS COMUNES') }}
                        @include('buttons.help', [
                            'helpId' => 'GeneralParams',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/general_parameters.json',
                                'asset'),
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Configuración de Roles --}}
                        <project-tracking-staff-classification></project-tracking-staff-classification>
                        @if (!Module::has('Payroll'))
                            {{-- Cargos --}}
                            <project-tracking-positions></project-tracking-positions>
                            {{-- Personal --}}
                            <project-tracking-personal-register></project-tracking-personal-register>
                        @endif
                        {{-- Configuración de tipos de proyectos --}}
                        <project-tracking-project-type></project-tracking-project-type>
                        {{-- Configuración de tipos de productos --}}
                        <project-tracking-type-products></project-tracking-type-products>
                        {{-- Dependencias --}}
                        <project-tracking-dependencies></project-tracking-dependencies>
                        {{-- Prioridades --}}
                        <project-tracking-priorities></project-tracking-priorities>
                        {{-- Configuración de proyectos --}}
                        <project-tracking-projects></project-tracking-projects>
                        {{-- Subproyectos --}}
                        <project-tracking-subprojects></project-tracking-subprojects>
                        {{-- Configuración de productos --}}
                        <project-tracking-products></project-tracking-products>
                        {{-- Configuración de Actividades --}}
                        <project-tracking-activity-config></project-tracking-activity-config>
                        {{-- Configuración de Estatus de Actividades --}}
                        <project-tracking-activity-status></project-tracking-activity-status>
                        {{-- Configuración de Estatus de Entrega --}}
                        <project-tracking-delivery-status></project-tracking-delivery-status>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
