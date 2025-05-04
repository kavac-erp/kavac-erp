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
    Plan de compra
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Gestión de plan de compra') }}
                        @include('buttons.help', [
                            'helpId' => 'purchasePlans',
                            'helpSteps' => get_json_resource('ui-guides/plans/purchase_plans.json', 'purchase')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body" id="helpPurchasePlans">
                    @if(!isset($record_edit))
                        <purchase-plan-form 
                            :purchase_process="{{ $purchase_process }}"
                            :users="{{ $users }}" 
                            :purchase_types="{{ $purchase_types }}" 
                            route_list="{{ url('purchase/purchase_plans') }}" />
                    @else
                        <purchase-plan-form 
                            :purchase_process="{{ $purchase_process }}"
                            :users="{{ $users }}" 
                            :purchase_types="{{ $purchase_types }}"
                            :record_edit="{{ $record_edit }}"
                            route_list="{{ url('purchase/purchase_plans') }}"  />
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
