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
    {{ __('Compromiso') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Compromiso') }}
                        @include('buttons.help', [
                            'helpId' => 'compromise',
                            'helpSteps' => get_json_resource('ui-guides/budget_compromise.json', 'budget')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <budget-compromise
                    edit_object ="{{ (isset($budgetCompromise)) ? $budgetCompromise : '' }}" route_list="{{ url('budget/compromises') }}"
                    route_edit="{{ url('budget/compromises/{id}/edit') }}"
                    accounting="{{ Module::has('Accounting') && Module::isEnabled('Accounting') }}"
                    budget_class="{{ 'Modules\Budget\Models\BudgetCompromise' }}"
                ></budget-compromise>
            </div>
        </div>
    </div>
@stop
