@extends('budget::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    Presupuesto
@stop

@section('maproute-title')
    Reporte Mayor Analítico
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="budgetAnalyticalMajor">
                <div class="card-header">
                    <h6 class="card-title">
                        Mayor Analítico
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <budget-analytical-major
                    url="{{ route('budget.report.budgetAnalyticalMajorPdf') }}"
                    budget-items="{{ $budgetItems }}"
                    budget-projects="{{ $budgetProjects }}"
                    budget-centralized-actions="{{ $budgetCentralizedActions }}"
                />
            </div>
        </div>
    </div>
@stop
