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
    Cotizaciones
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Listado de cotizaciones</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('purchase.quotation.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <purchase-quotation-list
                        :employments ="{{ $employments }}"
                        :has_budget="{{ isset($has_budget)?'true':'false' }}"
                        :record_lists="{{ $records }}"
                        route_edit="{{ url('purchase/quotation/{id}/edit') }}"
                    />
                </div>
            </div>
        </div>
    </div>
@stop
