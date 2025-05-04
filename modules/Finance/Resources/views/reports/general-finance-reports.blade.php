@extends('finance::layouts.master')

@section('maproute-icon')
	<i class="now-ui-icons business_chart-bar-32"></i>
@stop

@section('maproute-icon-mini')
	<i class="now-ui-icons business_chart-bar-32"></i>
@stop

@section('maproute-actual')
	Finanzas
@stop

@section('maproute-title')
	Reportes de Finanzas
@stop


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpGanaralfinanceReports">
                <div class="card-header">
                    <h6 class="card-title">
                        Reporte general de finanzas
                    </h6>
                    <div class="card-btns">
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <finance-general-reports></finance-general-reports>
                </div>
            </div>
        </div>
    </div>
@stop
