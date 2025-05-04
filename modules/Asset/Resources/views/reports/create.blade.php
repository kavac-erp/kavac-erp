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
            <div id="cardAssetForm" class="card">
                <div class="card-header">
                    <h6 class="card-title text-uppercase">Reportes de Bienes
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
                <asset-report-create route_list="{{ url('asset/registers/vue-list') }}"
                    institution_id="{!! isset($institution) ? $institution->id : 'null' !!}">
                </asset-report-create>
            </div>
        </div>
    </div>
@stop
