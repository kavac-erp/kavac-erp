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
    Planes de compras
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Listado de planes de compras</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('purchase.purchase_plans.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <purchase-plan-list
                        :record_list="{{ $record_list }}"
                        route_edit="{{ url('purchase/purchase_plans/{id}/edit') }}"
                    />
                </div>
            </div>
        </div>
    </div>
@stop
