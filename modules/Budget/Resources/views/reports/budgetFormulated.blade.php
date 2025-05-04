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
    Reporte de Presupuesto Formulado
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="budgetAvailability">
                <div class="card-header">
                    <h6 class="card-title">
                        Presupuesto Formulado
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <budget-formulated-report
                    url="{{ route('budget.report.formulated.data') }}"
                    pdf="{{ route('budget.report.formulated.pdf') }}"
                    formulations-url="{{ route('budget.formulations') }}"
                    budget-projects="{{ $budgetProjects }}"
                    budget-centralized-actions="{{ $budgetCentralizedActions }}"
                    :years="{{ $years }}"
                    :errors="{{json_encode($errors->all())}}"
                />
                </budget-formulated-report>
            </div>
        </div>
    </div>
@stop
