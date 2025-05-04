@extends('purchase::layouts.master')

@section('maproute-icon')
    <i class="ion-social-dropbox-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-social-dropbox-outline"></i>
@stop

@section('maproute-actual')
    Compras
@stop

@section('maproute-title')
    Requerimientos
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                    {{ __('Gestión de presupuesto base') }}
                        @include('buttons.help', [
                            'helpId' => 'purchasePlans',
                            'helpSteps' => get_json_resource('ui-guides/requirements/purchase_base_budget.json', 'purchase')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => route('purchase.requirements.index')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div id="helpPurchaseBaseBudget">
                    @if(isset($baseBudget))
                        <purchase-base-budget-form
                            :records="{{ $requirements }}"
                            :base_budget_edit="{{ $baseBudget }}"
                            :employments="{{ $employments }}"
                        />
                    @else
                        <purchase-base-budget-form 
                            :records="{{ $requirements }}"
                        />
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
