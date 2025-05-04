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
    Orden de compra / servicio
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                    {{ __('Gestión de orden de compra / servicio') }}
                        @include('buttons.help', [
                            'helpId' => 'purchaseDirectHire',
                            'helpSteps' => get_json_resource('ui-guides/purchase_order/purchase_direct_hire.json', 'purchase')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                @if(!isset($record_edit))
                    <purchase-order-direct-hire-form
                        :quotations="{{ $quotations }}"
                        :tax="{{ $tax }}"
                        :tax_unit="{{ $tax_unit }}"
                        :department_list="{{ $department_list }}"
                        :employments="{{ $employments }}"
                        :purchase_supplier_objects="{{ $purchase_supplier_objects }}"
                        :suppliers="{{ $suppliers }}"
                        route_list="/purchase/purchase_order"
                    />
                @else
                    <purchase-order-direct-hire-form
                        :quotations="{{ $requirements }}"
                        :tax="{{ $tax }}"
                        :tax_unit="{{ $tax_unit }}"
                        :department_list="{{ $department_list }}"
                        :employments="{{ $employments }}"
                        :purchase_supplier_objects="{{ $purchase_supplier_objects }}"
                        :suppliers="{{ $suppliers }}"
                        :record_edit="{{ $record_edit }}"
                        route_list="/purchase/purchase_order"
                    />
                @endif
            </div>
        </div>
    </div>
@stop
