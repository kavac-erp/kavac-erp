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
    {{ __('Acciones Específicas') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Acción Específica') }}
                        @include('buttons.help', [
                            'helpId' => 'specificActions',
                            'helpSteps' => get_json_resource('ui-guides/budget_specific_actions.json', 'budget')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                {!! (!isset($model)) ? Form::open($header) : Form::model($model, $header) !!}
                    <div class="card-body">
                        @include('layouts.form-errors')
                        <div class="row">
                            <div class="col-6">
                                <div id="project_sel_help" class="form-group">
                                    {!! Form::label('project', __('Proyecto')) !!}
                                    <div class="custom-control custom-switch">
                                        {!! Form::radio(
                                            'project_centralized_action',
                                            'project',
                                            (isset($model) && strpos($model->specificable_type, 'Project')) ? true : null,
                                            [
                                                'id' => 'sel_project',
                                                'class' => 'custom-control-input sel_project_centralized_action'
                                            ]
                                        ) !!}
                                        <label class="custom-control-label" for="sel_project"></label>
                                    </div>
                                </div>
                                <div id="project_id_help" class="form-group">
                                    {!! Form::select(
                                        'project_id',
                                        $projects,
                                        (isset($model) && strpos($model->specificable_type, 'Project')) ? $model->specificable_id : old('project_id'),
                                        [
                                            'id' => 'project_id',
                                            'class' => 'select2', 'data-toggle' => 'tooltip',
                                            'title' => __('Seleccione un proyecto'),
                                            'disabled' => (!$errors->has('project_id')) ? 'disabled' : false,
                                            'onchange' => 'minDateProject()'
                                        ]
                                    ) !!}
                                </div>
                            </div>
                            <div class="col-6">
                                <div id="centralized_action_help" class="form-group">
                                    {!! Form::label('centralized_action', __('Acción Centralizada')) !!}
                                    <div class="custom-control custom-switch">
                                        {!! Form::radio(
                                            'project_centralized_action',
                                            'centralized_action',
                                            (isset($model) && strpos($model->specificable_type,
                                            'CentralizedAction')) ? true : null,
                                            [
                                                'id' => 'sel_centralized_action',
                                                'class' => 'custom-control-input sel_project_centralized_action'
                                            ]
                                        ) !!}
                                        <label class="custom-control-label" for="sel_centralized_action"></label>
                                    </div>
                                </div>
                                <div id="centralized_action_id_help" class="form-group">
                                    {!! Form::select(
                                        'centralized_action_id',
                                        $centralized_actions,
                                        (isset($model) && strpos($model->specificable_type, 'CentralizedAction')) ? $model->specificable_id : old('centralized_action_id'),
                                        [
                                            'id' => 'centralized_action_id',
                                            'class' => 'select2', 'data-toggle' => 'tooltip',
                                            'title' => __('Seleccione una acción centralizada'),
                                            'disabled' => (!$errors->has('centralized_action_id')) ? 'disabled' : false,
                                            'onchange' => 'minDateCentralizedAction()'
                                        ]
                                    ) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <div id="from_date_help" class="form-group is-required">
                                    {!! Form::label('from_date', __('Fecha de inicio'), ['class' => 'control-label']) !!}
                                    {!! Form::date('from_date', (isset($model)) ? $model->from_date : date('Y-m-d'), [
                                        'id' => 'from_date',
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha en la que inicia la acción específica'),
                                        'onchange' => 'maxDate()'
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-2">
                                <div id="to_date_help" class="form-group is-required">
                                    {!! Form::label('to_date', __('Fecha de finalización'), ['class' => 'control-label']) !!}
                                    {!! Form::date('to_date', (isset($model))?$model->to_date:old('to_date'), [
                                        'id' => 'to_date',
                                        'class' => 'form-control input-sm no-restrict',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => 'dd/mm/YYYY',
                                        'title' => __('Fecha en la que finaliza la acción específica')
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-2">
                                <div id="code_help" class="form-group is-required">
                                    {!! Form::label('code', __('Código'), ['class' => 'control-label']) !!}
                                    {!! Form::text('code', old('code'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => __('Código de la acción específica'),
                                        'title' => __('Código que identifica la acción específica')
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-6">
                                <div id="name_help" class="form-group is-required">
                                    {!! Form::label('name', __('Nombre'), ['class' => 'control-label']) !!}
                                    {!! Form::text('name', old('name'), [
                                        'class' => 'form-control input-sm',
                                        'data-toggle' => 'tooltip',
                                        'placeholder' => __('Nombre de la acción específica'),
                                        'title' => __('Nombre que identifica la acción específica')
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="helpStatus" class="col-6">
                                <div class="form-group">
                                    <label for="active" class="control-label">{{ __('Activo') }}</label>
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
                            <div class="col-12">
                                <div id="description_help" class="form-group is-required">
                                    {!! Form::label('description', __('Descripción'), ['class' => 'control-label']) !!}
                                    <ckeditor :editor="ckeditor.editor" id="description" data-toggle="tooltip"
                                              title="{!! __('Descripción de la acción específica') !!}"
                                              :config="ckeditor.editorConfig" class="form-control" name="description"
                                              tag-name="textarea" rows="4" ref="descriptionEditor"
                                              v-model="ckeditor.editorData"
                                              placeholder="{!! __('Descripción de la acción específica') !!}"></ckeditor>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
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
                            'title' => __('Cancelar y regresar'),
                            'onclick' => 'window.location.href="' . url()->previous() . '"'
                        ]) !!}
                        @endif
                        @if (!isset($hide_save) || !$hide_save)
                            {!! Form::button('<i class="fa fa-save"></i>', [
                                'class' => 'btn btn-success btn-icon btn-round',
                                'data-toggle' => 'tooltip',
                                'title' => __('Guardar registro'),
                                'type' => 'submit'
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
            app.ckeditor.editorData = "{!! (isset($model)) ? $model->description : old('description')  !!}";
            $('.sel_project_centralized_action').on('change', function(e) {
                $('#project_id').attr('disabled', (e.target.id!=="sel_project"));
                $('#centralized_action_id').attr('disabled', (e.target.id!=="sel_centralized_action"));

                if (e.target.id === "sel_project") {
                    $("#centralized_action_id").closest('.form-group').removeClass('is-required');
                    $("#project_id").closest('.form-group').addClass('is-required');
                }
                else if (e.target.id === "sel_centralized_action") {
                    $("#centralized_action_id").closest('.form-group').addClass('is-required');
                    $("#project_id").closest('.form-group').removeClass('is-required');
                }
            });

            if ($('.sel_project_centralized_action').is(':checked')) {
                if ($('#project_id').val() !== '') {
                    $('#project_id').attr('disabled', false);
                }

                if ($('#centralized_action_id').val() !== '') {
                    $('#centralized_action_id').attr('disabled', false);
                }

                $("#from_date").on('change', function() {
                    $("#to_date").attr("min", $(this).val());
                });
                $("#to_date").on('change', function() {
                    $("#from_date").attr("max", $(this).val());
                });
            }
            $("#reset-select").on('click', function() {
                $('#project_id').val('').change();
                $('#project_id').prop('disabled', true);
                    $('#centralized_action_id').val('').change();
                $('#centralized_action_id').prop('disabled', true);
                $('input[name=project_centralized_action]').attr('checked',false);
                $('input[name=from_date]').attr('value', '');
                $('input[name=to_date]').attr('value', '');
                $('input[name=code]').attr('value', '');
                $('input[name=name]').attr('value', '');
                app.ckeditor.editorData = '';
            });
        });
        function minDateProject() {
            for (let date of {!! ($projects_date) !!}) {
                if (document.getElementById('project_id').value == date.id) {
                    document.getElementById('from_date').min = date.from_date;
                    document.getElementById('from_date').max = date.to_date;
                    document.getElementById('to_date').min = date.from_date;
                    document.getElementById('to_date').max = date.to_date;
                }
            }
        }
        function minDateCentralizedAction() {
            for (let date of {!! ($centralized_actions_date) !!}) {
                if (document.getElementById('centralized_action_id').value == date.id) {
                    document.getElementById('from_date').min = date.from_date;
                    document.getElementById('from_date').max = date.to_date;
                    document.getElementById('to_date').min = date.from_date;
                    document.getElementById('to_date').max = date.to_date;
                }
            }
        }
        function maxDate() {
            let min = document.getElementById('from_date').value;
            document.getElementById('to_date').min = min;
        }
    </script>
@endsection
