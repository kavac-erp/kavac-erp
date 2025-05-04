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
    @permission('asset.depreciation.report')
        <div class="row">
            <div class="col-12">
                <div id="cardAssetForm" class="card">
                    <div class="card-header">
                        <h6 class="card-title text-uppercase">Depreciación Acumulada
                            @include('buttons.help', [
                                'helpId' => 'AssetReportForm',
                                'helpSteps' => get_json_resource('ui-guides/reports/report_form.json', 'asset'),
                            ])
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <asset-report-depreciation>
                    </asset-report-depreciation>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div id="cardAssetForm" class="card">
                    <div class="card-header">
                        <h6 class="card-title text-uppercase">Tabla de Depreciación
                            @include('buttons.help', [
                                'helpId' => 'AssetReportForm',
                                'helpSteps' => get_json_resource('ui-guides/reports/report_form.json', 'asset'),
                            ])
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <asset-report-depreciation-table>
                    </asset-report-depreciation-table>
                </div>
            </div>
        </div>
        </div>
    @endpermission
@stop
