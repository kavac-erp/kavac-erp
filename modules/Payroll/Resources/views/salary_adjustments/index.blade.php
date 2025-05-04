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
    Gestión de tablas salariales
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Ajustes en tablas salariales</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.salary-adjustments.create')])
                        @permission('payroll.salary.adjustments.import')
                            {!! Form::button('<i class="fa fa-upload"></i>', [
                                'class'       => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type'        => 'button',
                                'title'       => __('Importar registros'),
                                'onclick'     => "$('input[name=importFile]').click()"
                            ]) !!}
                            <input
                                id="importFile" name="importFile"
                                type="file"
                                style="display:none"
                                onchange="importData()"
                            >
						@endpermission
                        @permission('payroll.salary.adjustments.export')
                            {!! Form::button('<i class="fa fa-download"></i>', [
                                'class'       => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type'        => 'button',
                                'title'       => "Presione para descargar el documento con la información de los registros.",
                                'onclick'     => "exportData()"
                            ]) !!}
						@endpermission
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-12">
                        <payroll-salary-adjustments-list route_list="{{ url('payroll/salary-adjustments/vue-list') }}"
                                              route_delete="{{ url('payroll/salary-adjustments') }}"
                                              route_edit="{{ url('payroll/salary-adjustments/{id}/edit') }}"
                                              route_show="{{ url('payroll/salary-adjustments/{id}') }}">
                        </payroll-salary-adjustments-list>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('extra-js')
    <script type="text/javascript">
        var records;

        function exportData() {
            location.href = `${window.app_url}/payroll/salary-adjustments/export`;
        }

        function importData() {
            var url = `${window.app_url}/payroll/salary-adjustments/import`;
            var formData = new FormData();
            var importFile = document.querySelector('#importFile');
            formData.append("file", importFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                console.log(response.data);
                var texterror = 'Registro almacenado con exito';
                if (typeof response.data.errors !== 'undefined' && response.data.errors.length > 0) {
                    texterror = "Registros almacenados con exito, se encontraron " + response.data.errors.length + "errores, por favor revise la consola del navegador y/o correo enviado con los errores correspondientes";
                }
                $.gritter.add({
                    title: 'Exito!',
                    text: texterror,
                    class_name: 'growl-success',
                    image: "/images/screen-ok.png",
                    sticky: false,
                    time: 3500
                });
            }).catch(error => {
                console.log('failure');
                array_error = error.response.data.errors ? error.response.data.errors[0].errors[0] : '';
                $.gritter.add({
                    title: 'Advertencia!',
                    text: 'Error al importar el archivo: ' + array_error,
                    class_name: 'growl-danger',
                    image: "{{ asset('images/screen-warning.png') }}",
                    sticky: false,
                    time: 2000
                });
                console.log(error);
            });
        }
    </script>
@stop