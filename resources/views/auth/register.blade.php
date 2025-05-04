@extends('layouts.app')

@section('maproute-icon')
    <i class="fa fa-user"></i>
@stop

@section('maproute-icon-mini')
    <i class="fa fa-user"></i>
@stop

@section('maproute-actual')
    {{ __('Usuarios') }}
@stop

@section('maproute-title')
    {{ __('Usuarios') }}
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">
            @if (isset($model))
                {{ __('Actualizar usuario') }}
            @else
                {{ __('Registrar usuario') }}
            @endif
            </h6>
            <div class="card-btns">
                @include('buttons.previous', ['route' => url()->previous()])
                @include('buttons.minimize')
            </div>
        </div>
        @if (!isset($model)) {!! Form::open($header) !!} @else {!! Form::model($model, $header) !!} @endif
            {!! Form::token() !!}
            <div class="card-body">
                @include('layouts.form-errors')
                <div class="row">
                    <div class="col-6">
                        <div class="form-group is-required">
                            {!! Form::label('institution_id', __('Institución'), []) !!}
                            {!! Form::select('institution_id', (isset($institutions))?$institutions:[], isset($model) && $model->profile ? $model->profile->institution_id : old('institution_id'), [
                                    'class' => 'form-control select2',
                                    'id' => 'institution_id',
                                    'disabled' => (isset($model) && $model->profile!==null && $model->profile->institution_id!==null) ? true : false,
                                    'oninput' => 'updateStaffSelect($(this), $("#staff"))'
                                ]
                            ) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group" id="user">
                            {!! Form::label('staff', __('Empleado'), []) !!}

                            {{-- Update --}}
                            @if(isset($model) && $model->profile && $model->profile->employee_id)
                                {!! Form::select('staff', ['' => 'Seleccione...'] + $allPersons->toArray(), $model->profile->employee_id, [
                                    'class' => 'form-control select2', 'onchange' => 'hasStaff()',
                                    'id' => 'staff',
                                    'disabled' => false,
                                    'data-old' => old('staff')
                                ]) !!}
                            {{-- Create --}}
                            @else
                                {!! Form::select('staff', ['' => 'Seleccione...'] + $allPersons->toArray(), null, [
                                    'class' => 'form-control select2', 'onchange' => 'hasStaff()',
                                    'id' => 'staff',
                                    'disabled' => (isset($model) && $model->profile !== null) ? false : true,
                                    'data-old' => old('staff')
                                ]) !!}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 staff_name">
                        <div class="form-group is-required" id="user_first_name">
                            {!! Form::label('first_name', __('Nombre'), ['id' => 'first_name_label']) !!}
                            {!! Form::text('first_name', (isset($model) && $model->profile!==null)?$model->profile->first_name:old('first_name'), [
                                'class' => 'form-control input-sm', 'id' => 'first_name', 'data-toggle' => 'tooltip',
                                'title' => __('Indique el Nombre completo de la persona')
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group is-required" id="email">
                            {!! Form::label('email', __('Correo electrónico'), []) !!}
                            {!! Form::text('email', (isset($model))?$model->email:old('email'), [
                                'class' => 'form-control input-sm',
                                'data-toggle' => 'tooltip',
                                'title' => __('Indique el correo electrónico al cual envíar los datos de acceso')
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group is-required" id="user_name">
                            {!! Form::label('username', __('Usuario'), []) !!}
                            {!! Form::text('username', (isset($model))?$model->username:old('username'), [
                                'class' => 'form-control input-sm',
                                'data-toggle' => 'tooltip',
                                'title' => __('Indique el nombre de usuario')
                            ]) !!}
                        </div>
                    </div>
                </div>
                @include('auth.roles-permissions', ['user' => $model ?? null])
            </div>
            <div class="card-footer text-right" id="buttons">
                @include('buttons.form-display')
                @include('layouts.form-buttons')
            </div>
        {!! Form::close() !!}
    </div>
@endsection

@section('extra-js')
    @parent
    <script>
        /**
         * Muestra u oculta el campo de nombre si no se ha seleccionado un empleado
         *
         * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         */
        var hasStaff = () => {
            $(".staff_name").show();
            if ($('#staff').val() !== "") {
                $(".staff_name").hide();
            }
        }

        /**
         * Actualiza información de un select a partir de otro
         *
         * @param  {object}  parent_element Objeto con los datos del elemento que genera la acción
         * @param  {object}  target_element Objeto que se cargara con la información
         * @param  {string}  target_model   Modelo en el cual se va a realizar la consulta
         * @param  {string}  module_name    Nombre del módulo que ejecuta la acción
         */
        function updateStaffSelect(parent_element, target_element, edit) {
            var module_name = (typeof(module_name) !== "undefined") ? '/' + module_name : '';
            var parent_id = parent_element.val();
            var parent_name = parent_element.attr('id');

            target_element.empty();

            if (parent_id) {
                axios.get(
                    `/get-select-data-staff/${parent_name}/${parent_id}`
                ).then(response => {
                    if (response.data.result) {
                        target_element.attr('disabled', false);
                        target_element.empty().append('<option value="">{{ __('Seleccione...') }}</option>');
                        var filteredRecords = response.data.records.filter(record => record.employee_id !== null && record.user_id === null);
                        $.each(filteredRecords, function(index, record) {
                            target_element.append(
                                `<option value="${record['id']}">${record['first_name']} ${record['last_name']}</option>`
                            );
                            if (edit) {
                                let staff = document.getElementById('staff');
                                let staffOld = staff.getAttribute('data-old');
                                let staffValues = Object.values(staff);
                                for (let value of staffValues) {
                                    if (record['id'] == staffOld && staffOld == value.value) {
                                        value.selected = true;
                                    }
                                }
                            }
                        });
                    }
                }).catch(error => {
                    logs('app', 244, error, 'updateSelect');
                })
            } else {
                target_element.attr('disabled', true);
            }
        }
        @if (old('staff'))
            const timeOpen = setTimeout(addInstitutionId, 3000);
            function addInstitutionId () {
                updateStaffSelect($('#institution_id'), $('#staff'), true);
            }
        @endif
    </script>
@endsection
