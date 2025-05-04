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
    {{ __('Proyectos') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Proyecto') }}
                        @include('buttons.help', [
                            'helpId' => 'BudgetProjectHelp',
                            'helpSteps' => get_json_resource('ui-guides/budget_project.json', 'budget')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! (!isset($model))?Form::open($header):Form::model($model, $header) !!}
                    <div class="card-body">
                        @include('layouts.form-errors')
                        <div class="row">
                            <div id="helpInstitution" class="col-12 col-lg-3">
                                <div class="form-group is-required">
                                    {!! Form::label('institution_id', __('Institución'), ['class' => 'control-label']) !!}
                                    {!! Form::select(
                                        'institution_id',
                                        $institutions, null,
                                        [
                                            'id' => 'institution_id',
                                            'class' => 'select2', 'data-toggle' => 'tooltip',
                                            'onchange' => 'updateSelectActive($(this), $("#department_id"), "Department", undefined, undefined, [$("#payroll_position_id"), $("#payroll_staff_id")] )',
                                            'title' => __('Seleccione una institución')
                                        ]
                                    ) !!}
                                </div>
                            </div>
                            @php
                                $institution = count($institutions);
                            @endphp

                            @if($institution > 1)
                                <div id="helpDepartment" class="col-12 col-lg-3">
                                    <div class="form-group is-required">
                                        {!! Form::label('department_id', __('Dependencia'), ['class' => 'control-label']) !!}
                                        {!! Form::select('department_id', $departments, null, [
                                            'id' => 'department_id',
                                            'class' => 'select2',
                                            'data-toggle' => 'tooltip',
                                            'onchange' => 'updateStaffSelect($(this), $("#payroll_staff_id"), "PayrollEmployment", "Payroll", "payrollStaff", [$("#payroll_position_id")])',
                                            'title' => __('Seleccione un departamento o dependencia'),
                                        ]) !!}
                                    </div>
                                </div>
                                @if (Module::has('Payroll') && Module::isEnabled('Payroll'))
                                    <div id="helpResponsible" class="col-12 col-lg-3">
                                        <div class="form-group is-required">
                                            {!! Form::label('payroll_staff_id', __('Responsable'), ['class' => 'control-label']) !!}
                                            {!! Form::select('payroll_staff_id', $staffs, null, [
                                                'id' => 'payroll_staff_id',
                                                'class' => 'select2',
                                                'data-toggle' => 'tooltip',
                                                'onchange' => 'updateSelectCustomPosition($(this), $("#payroll_position_id"), "PayrollEmployment", "Payroll", "")',
                                                'title' => __('Seleccione una persona responsable del proyecto')
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div id="helpPosition" class="col-12 col-lg-3">
                                        <div class="form-group is-required">
                                            {!! Form::label('payroll_position_id', __('Cargo de Responsable'), [
                                                'class' => 'control-label'
                                            ]) !!}
                                            {!! Form::select('payroll_position_id', $positions, null, [
                                                'id' => 'payroll_position_id',
                                                'class' => 'select2',
                                                'data-toggle' => 'tooltip',
                                                'disabled' => ( true),
                                                'title' => __('Seleccione el cargo de la persona responsable del proyecto')
                                            ]) !!}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div id="helpDepartment" class="col-12 col-lg-3">
                                    <div class="form-group is-required">
                                        {!! Form::label('department_id', __('Dependencia'), ['class' => 'control-label']) !!}
                                        {!! Form::select('department_id', $departments, null, [
                                            'id' => 'department_id',
                                            'class' => 'select2',
                                            'data-toggle' => 'tooltip',
                                            'disabled' => ( false),
                                            'onchange' => 'updateStaffSelect($(this), $("#payroll_staff_id"), "PayrollEmployment", "Payroll", "payrollStaff",[$("#payroll_position_id")])',
                                            'title' => __('Seleccione un departamento o dependencia'),
                                        ]) !!}
                                    </div>
                                </div>
                                @if (Module::has('Payroll') && Module::isEnabled('Payroll'))
                                    <div id="helpResponsible" class="col-12 col-lg-3">
                                        <div class="form-group is-required">
                                            {!! Form::label('payroll_staff_id', __('Responsable'), ['class' => 'control-label']) !!}
                                            {!! Form::select('payroll_staff_id', $staffs, null, [
                                                'id' => 'payroll_staff_id',
                                                'class' => 'select2',
                                                'data-toggle' => 'tooltip',
                                                'disabled' => ( false),
                                                'onchange' => 'updateSelectCustomPosition($(this), $("#payroll_position_id"), "PayrollEmployment", "Payroll", "")',
                                                'title' => __('Seleccione una persona responsable del proyecto')
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div id="helpPosition" class="col-12 col-lg-3">
                                        <div class="form-group is-required">
                                            {!! Form::label('payroll_position_id', __('Cargo de Responsable'), [
                                                'class' => 'control-label'
                                            ]) !!}
                                            {!! Form::select('payroll_position_id', $positions, null, [
                                                'id' => 'payroll_position_id',
                                                'class' => 'select2',
                                                'data-toggle' => 'tooltip',
                                                'disabled' => ( true),
                                                'title' => __('Seleccione el cargo de la persona responsable del proyecto')
                                            ]) !!}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="row">
                            <div id="helpCode" class="col-12 col-lg-3">
                                <div class="form-group is-required">
                                    {!! Form::label('code', __('Código'), ['class' => 'control-label']) !!}
                                    {!! Form::text('code', (isset($model)) ? $model->code : old('code'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => __('Código de proyecto'),
                                        'title' => __('Código que identifica el proyecto')
                                    ]) !!}
                                </div>
                            </div>
                            @if(@config('institution.use_onapre'))
                                <div id="helpOnapreCode" class="col-12 col-lg-3">
                                    <div class="form-group">
                                        {!! Form::label('onapre_code', __('Código ONAPRE'), ['class' => 'control-label']) !!}
                                        {!! Form::text('onapre_code', (isset($model)) ? $model->onapre_code : old('onapre_code'), [
                                            'class' => 'form-control input-sm',
                                            'data-toggle' => 'tooltip',
                                            'placeholder' => __('Código de la ONAPRE'),
                                            'title' => __('Código asignado por la Oficina Nacional de Presupuesto (ONAPRE)')
                                        ]) !!}
                                    </div>
                                </div>
                            @endif
                            <div id="helpName" class="col-12 col-lg-{{ config('institution.use_onapre') ? '6' : '9'}}">
                                <div class="form-group is-required">
                                    {!! Form::label('name', __('Nombre'), ['class' => 'control-label']) !!}
                                    {!! Form::text('name', (isset($model)) ? $model->name : old('name'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => __('Nombre del proyecto'),
                                        'title' => __('Nombre que identifica el proyecto')
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div id="helpStartDate" class="form-group is-required">
                                    {!! Form::label('from_date', __('Fecha de inicio'), ['class' => 'control-label']) !!}
                                    {!! Form::date('from_date', (isset($model))?$model->from_date:date('Y-m-d'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha en la que inicia el proyecto')
                                    ]) !!}
                                </div>
                            </div>
                            <div id="helpEndDate" class="col-12 col-lg-3">
                                <div class="form-group is-required">
                                    {!! Form::label('to_date', __('Fecha de finalización'), ['class' => 'control-label']) !!}
                                    {!! Form::date('to_date', (isset($model))?$model->to_date:old('to_date'), [
                                        'class' => 'form-control input-sm no-restrict',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha en la que finaliza el proyecto')
                                    ]) !!}
                                </div>
                            </div>
                            <div id="helpStatus" class="col-12 col-lg-1">
                                <div class="form-group">
                                    <label for="" class="control-label">{{ __('Activo') }}</label>
                                    <div class="custom-control custom-switch">
                                        {!! Form::checkbox('active', true, (isset($model))?$model->active:null, [
                                            'id' => 'active',
                                            'class' => 'custom-control-input'
                                        ]) !!}
                                        <label class="custom-control-label" for="active"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="helpDescription" class="col-12">
                                <div class="form-group is-required">
                                    {!! Form::label('description', __('Descripción'), ['class' => 'control-label']) !!}
                                    <ckeditor
                                        id="description" class="form-control" name="description"
                                        data-toggle="tooltip" placeholder="{!! __('Descripción del proyecto') !!}"
                                        ref="descriptionEditor" rows="4" tag-name="textarea"
                                        title="{!! __('Descripción del proyecto') !!}"
                                        :config="ckeditor.editorConfig"
                                        :editor="ckeditor.editor"
                                        v-model="ckeditor.editorData">
                                    </ckeditor>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <!-- @include('layouts.form-buttons') -->
                        @if (!isset($hide_clear) || !$hide_clear)
                            {!! Form::button('<i class="fa fa-eraser"></i>', [
                                'id' => 'reset-select',
                                'class' => 'btn btn-default btn-icon btn-round',
                                'type' => 'reset',
                                'data-toggle' => 'tooltip',
                                'title' => __('Borrar datos del formulario'),
                            ]) !!}
                        @endif
                        @if (!isset($hide_previous) || !$hide_previous)
                        {!! Form::button('<i class="fa fa-ban"></i>', [
                            'class' => 'btn btn-warning btn-icon btn-round',
                            'type' => 'button',
                            'data-toggle' => 'tooltip',
                            'onclick' => 'window.location.href="' . url()->previous() . '"',
                            'title' => __('Cancelar y regresar'),
                        ]) !!}
                        @endif
                        @if (!isset($hide_save) || !$hide_save)
                            {!! Form::button('<i class="fa fa-save"></i>', [
                                'class' => 'btn btn-success btn-icon btn-round',
                                'type' => 'submit',
                                'data-toggle' => 'tooltip',
                                'title' => __('Guardar registro')
                            ]) !!}
                        @endif
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
            app.ckeditor.editorData = "{!! (isset($model))?$model->description:old('description')  !!}";
            $("#reset-select").on('click', function() {
                $('#institution_id').val('').change();
                $('#department_id').val('').change();
                $('#payroll_staff_id').val('').change();
                $('#payroll_position_id').val('').change();
                app.ckeditor.editorData = '';
            });
        });

        $(document).on('submit', function() {
            $('#department_id').attr('disabled', false);
            $('#payroll_staff_id').attr('disabled', false);
            $('#payroll_position_id').attr('disabled', false);
        })
    </script>
@endsection