@extends('asset::layouts.master', ['setting_view' => true])

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Bienes') }}
@stop

@section('maproute-title')
    {{ __('Configuración') }}
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
                {!! Form::open(['id' => 'form-codes', 'route' => 'asset.setting.store', 'method' => 'post']) !!}
                {!! Form::token() !!}
                <div class="card-body">
                    @include('layouts.help-text', ['codeSetting' => true])
                    @include('layouts.form-errors')
                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('Gestión de Bienes') }}</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3" id="helpCodeAsignation">
                            <div class="form-group">
                                {!! Form::label('asignation_code', 'Código de las asignaciones', []) !!}
                                {!! Form::text('asignations_code', $asCode ? $asCode->format_code : old('asignations_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de las asignaciones',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $asCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" id="helpCodeDisincorporation">
                            <div class="form-group">
                                {!! Form::label('disincorporation_code', 'Código de las desincorporaciones', []) !!}
                                {!! Form::text('disincorporations_code', $dsCode ? $dsCode->format_code : old('disincorporations_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de las desincorporaciones',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $dsCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" id="helpCodeRequest">
                            <div class="form-group">
                                {!! Form::label('request_code', 'Código de las solicitudes', []) !!}
                                {!! Form::text('requests_code', $rqCode ? $rqCode->format_code : old('requests_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de las solicitudes',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $rqCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" id="helpCodeInventory">
                            <div class="form-group">
                                {!! Form::label('inventory_code', 'Código de los registros de inventario', []) !!}
                                {!! Form::text('inventories_code', $ivCode ? $ivCode->format_code : old('inventories_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de los registros de inventario',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $ivCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" id="helpCodeReport">
                            <div class="form-group">
                                {!! Form::label('report_code', 'Código de los reportes', []) !!}
                                {!! Form::text('reports_code', $rpCode ? $rpCode->format_code : old('reports_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de los reportes',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $rpCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" id="helpCodeDepreciationMethods">
                            <div class="form-group">
                                {!! Form::label('depreciation_code', 'Código de método de depreciación', []) !!}
                                {!! Form::text('depreciations_code', $dsCode ? $dsCode->format_code : old('depreciations_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de método de depreciación',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $dsCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" id="helpCodeDepreciations">
                            <div class="form-group">
                                {!! Form::label('depreciation_code', 'Código de depreciación', []) !!}
                                {!! Form::text('depreciations_code', $dpCode ? $dpCode->format_code : old('depreciations_code'), [
                                    'class' => 'form-control',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Formato para el código de depreciación',
                                    'placeholder' => 'Ej. XXX-00000000-YYYY',
                                    'readonly' => $dpCode ? true : false,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                @if (!$asCode || !$dsCode || !$rqCode || !$ivCode || !$rpCode || !$dpCode)
                    <div class="card-footer text-right">
                        @include('layouts.form-buttons')
                    </div>
                @endif
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpGeneralParamsForm">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Parámetros Generales') }}
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
                        {{-- Configuración de la Condición Física de un Bien --}}
                        <asset-conditions id="helpAssetConditions"></asset-conditions>

                        {{-- Configuración del Status de Uso de un Bien --}}
                        <asset-status id="helpAssetStatus"></asset-status>

                        {{-- Configuración de la Función de Uso de un Bien --}}
                        <asset-use-functions id="helpAssetUseFunctions"></asset-use-functions>

                        {{-- Configuración del Tipo de Adquisición de un Bien --}}
                        <asset-acquisition-types id="helpAssetAcquisitionTypes"></asset-acquisition-types>

                        {{-- Configuración del método de Depreciación --}}
                        <asset-depreciation-methods id="helpAssetDepreciationMethods"></asset-depreciation-methods>

                        {{-- Configuración de los Depósitos de bienes --}}
                        <asset-storages id="helpAssetStorages"></asset-storages>

                        {{-- Modal para manejar edificaciones --}}
                        <asset-buildings id="helpAssetBuildings"></asset-buildings>

                        {{-- Modal para manejar pisos o niveles de edificaciones --}}
                        <asset-floors id="helpAssetFloors"></asset-floors>

                        {{-- Modal para manejar secciones de los pisos o niveles de edificaciones --}}
                        <asset-sections id="helpAssetSections"></asset-sections>
                        @if (!Module::has('Purchase') || !Module::isEnabled('Purchase'))
                            <asset-supplier-branches-fix id="helpAssetSupplierBranches"></asset-supplier-branches-fix>
                            <asset-supplier-types-fix id="helpAssetSupplierTypes"></asset-supplier-types-fix>
                            <asset-supplier-specialties-fix
                                id="helpAssetSupplierSpecialties"></asset-supplier-specialties-fix>
                            <asset-supplier-objects-fix id="helpAssetSupplierObjects"></asset-supplier-objects-fix>
                            <required-documents module="purchase" model="supplier" id="required_documents"
                                short_name_component="Documentos requeridos" name_component="documentos requeridos"
                                title="Registro de documentos requeridos" typedoc= "true">
                            </required-documents>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Parámetros Específicos del Clasificador de Bienes') }}
                        @include('buttons.help', [
                            'helpId' => 'EspecificParams',
                            'helpSteps' => get_json_resource(
                                'ui-guides/settings/specific_parameters.json',
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
                        {{-- Configuración de Tipos de Bienes --}}
                        <asset-types id="helpAssetTypes"></asset-types>

                        {{-- Configuración de Categorías Generales de Bienes --}}
                        <asset-categories id="helpAssetCategories"></asset-categories>

                        {{-- Configuración de Subcategorías de Bienes --}}
                        @if (Module::has('Accounting') && Module::isEnabled('Accounting'))
                            <asset-subcategories id="helpAssetSubcategories"
                                :accounting="{{ Module::has('Accounting') && Module::isEnabled('Accounting') }}">
                            </asset-subcategories>
                        @else
                            <asset-subcategories id="helpAssetSubcategories"></asset-subcategories>
                        @endif

                        {{-- Configuración de Categorías Específicas de Bienes --}}
                        <asset-specific-categories id="helpAssetSpecificCategories"></asset-specific-categories>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('extra-js')
    @parent
    {!! Html::script('js/ckeditor.js', [], Request::secure()) !!}
@stop
