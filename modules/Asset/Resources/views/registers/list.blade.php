@extends('asset::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-actual')
    Bienes
@stop

@section('maproute-title')
    Gestión de Bienes
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Bienes</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('asset.register.create')])
                        @permission('asset.import')
                            {!! Form::button('<i class="fa fa-upload"></i>', [
                                'class' => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type' => 'button',
                                'title' => __('Importar registros'),
                                'onclick' => "toggleSection('assetImportSection', 'assetExportSection')",
                            ]) !!}
                            <input id="importFileMueble" name="importFileMueble" type="file" style="display:none"
                                onchange="importData('mueble', 'importFileMueble')">
                            <input id="importFileAuto" name="importFileAuto" type="file" style="display:none"
                                onchange="importData('vehiculo', 'importFileAuto')">
                            <input id="importFileInmueble" name="importFileInmueble" type="file" style="display:none"
                                onchange="importData('inmueble', 'importFileInmueble')">
                            <input id="importFileSemoviente" name="importFileSemoviente" type="file" style="display:none"
                                onchange="importData('semoviente', 'importFileSemoviente')">
                        @endpermission
                        @permission('asset.export')
                            {!! Form::button('<i class="fa fa-download"></i>', [
                                'class' => 'btn btn-sm btn-primary btn-custom',
                                'data-toggle' => 'tooltip',
                                'type' => 'button',
                                'title' => __('Exportar registros'),
                                'onclick' => "toggleSection('assetExportSection', 'assetImportSection')",
                            ]) !!}
                        @endpermission
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    @permission('asset.import')
                        <section id="assetImportSection" class="with-border with-radius mb-4"
                            style="display:none;padding:15px;">
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::button("<span aria-hidden='true'>×</span>", [
                                        'class' => 'close float-right',
                                        'type' => 'button',
                                        'onclick' => "toggleSection('assetImportSection')",
                                    ]) !!}
                                    <h6 class="text-center"> Tipo de registro a importar</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        onclick="$('input[name=importFileAuto]').click()"
                                        title="Carga de vehículos en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Vehículo</span>
                                    </a>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        onclick="$('input[name=importFileMueble]').click()"
                                        title="Carga de muebles en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Mueble</span>
                                    </a>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        onclick="$('input[name=importFileInmueble]').click()"
                                        title="Carga de inmuebles en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Inmueble</span>
                                    </a>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        onclick="$('input[name=importFileSemoviente]').click()"
                                        title="Carga de semovientes en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Semoviente</span>
                                    </a>
                                </div>
                            </div>
                        </section>
                    @endpermission
                    @permission('asset.export')
                        <section id="assetExportSection" class="with-border with-radius mb-4"
                            style="display:none;padding:15px;">
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::button("<span aria-hidden='true'>×</span>", [
                                        'class' => 'close float-right',
                                        'type' => 'button',
                                        'onclick' => "toggleSection('assetExportSection')",
                                    ]) !!}
                                    <h6 class="text-center"> Tipo de registro a exportar</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary" onclick="exportData('vehiculo')"
                                        title="Descarga de vehículos en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Vehículo</span>
                                    </a>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary" onclick="exportData('mueble')"
                                        title="Descarga de muebles en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Mueble</span>
                                    </a>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary" onclick="exportData('inmueble')"
                                        title="Descarga de inmuebles en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Inmueble</span>
                                    </a>
                                </div>
                                <div class="mx-auto d-block" style="cursor: pointer">
                                    <a class="btn-simplex btn-simplex-md btn-simplex-primary"
                                        onclick="exportData('semoviente')"
                                        title="Descarga de semovientes en registros de bienes" data-toggle="tooltip">
                                        <i class="icofont icofont-file-excel ico-3x"></i>
                                        <span class="mt-2">Semoviente</span>
                                    </a>
                                </div>
                            </div>
                        </section>
                    @endpermission
                    <asset-list route_list="{{ url('asset/registers/vue-list') }}"
                        route_edit="{{ url('asset/registers/edit/{id}') }}"
                        route_delete="{{ url('asset/registers/delete') }}">
                    </asset-list>
                </div>
            </div>
        </div>
    </div>
@stop

@section('extra-js')
    @parent
    <script>
        function exportData(type) {
            location.href = `${window.app_url}/asset/registers/export/all?type=${type}`;
        };

        function importData(type, input) {
            var url = `${window.app_url}/asset/registers/import/all?type=${type}`;
            var formData = new FormData();
            var importFile = document.querySelector('#' + input);
            formData.append("file", importFile.files[0]);
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                $.gritter.add({
                    title: 'Exito!',
                    text: 'Su solicitud esta en proceso, esto puede tardar unos minutos. Se le notificara al terminar la operación',
                    class_name: 'growl-primary',
                    image: "/images/screen-ok.png",
                    sticky: false,
                    time: 3500
                });

                importFile.value = '';
            }).catch(error => {
                console.log('failure');
                $.gritter.add({
                    title: 'Advertencia!',
                    text: 'Error al importar el archivo',
                    class_name: 'growl-danger',
                    image: "{{ asset('images/screen-warning.png') }}",
                    sticky: false,
                    time: 2000
                });
                console.log(error);


            });
        };

        function toggleSection($sectionName, $sectionNameOther) {
            var section = document.getElementById($sectionName);
            var sectionOther = document.getElementById($sectionNameOther);
            if (section.style.display === "none") {
                section.style.display = "block";
                sectionOther.style.display = "none";
            } else {
                section.style.display = "none";
            }
        }
    </script>
@endsection
