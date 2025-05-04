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
    Cotización
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                @if(!isset($record_edit))
                    <div class="card-header">
                        <h6 class="card-title">
                        {{ __('Gestión de cotización') }}
                            @include('buttons.help', [
                                'helpId' => 'purchaseQuotation',
                                'helpSteps' => get_json_resource('ui-guides/quotatiom/purchase_quotation.json', 'purchase')
                            ])
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <purchase-quotation-form-update
                        :record_budgets="{{ $record_base_budgets }}"
                        :tax="{{ $tax }}"
                        :tax_unit="{{ $tax_unit }}"
                        :suppliers="{{ $suppliers }}"
                        route_list="{{ url('purchase/quotation') }}"
                    />
                @else
                    <div class="card-header">
                        <h6 class="card-title">
                        {{ __('Actualizar cotización') }}
                            @include('buttons.help', [
                                'helpId' => 'purchaseQuotation',
                                'helpSteps' => get_json_resource('ui-guides/quotatiom/purchase_quotation.json', 'purchase')
                            ])
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <purchase-quotation-form-edit
                        :record_budgets="{{ $record_base_budgets }}"
                        :tax="{{ $tax }}"
                        :tax_unit="{{ $tax_unit }}"
                        :suppliers="{{ $suppliers }}"
                        :record_edit="{{ $record_edit }}"
                        :base_budget_edit="{{ $base_budget_edit }}"
                        route_list="{{ url('purchase/quotation') }}"
                    />
                @endif
            </div>
        </div>
    </div>
@stop
