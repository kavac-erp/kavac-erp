@extends('purchase::layouts.master')

@if (Module::has('Budget'))
    @section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
    @stop

    @section('maproute-icon-mini')
        <i class="ion-arrow-graph-up-right"></i>
    @stop

    @section('maproute-actual')
        Presupuesto
    @stop 
@else 
    @section('maproute-icon')
    <i class="ion-social-dropbox-outline"></i>
    @stop

    @section('maproute-icon-mini')
        <i class="ion-social-dropbox-outline"></i>
    @stop

    @section('maproute-actual')
        Compras
    @stop 
@endif

@section('maproute-title')
    Disponibilidad presupuestaria
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Disponibilidad presupuestaria</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <shared-quatation-list
                        :record_lists="{{ $records }}" 
                        route_edit="{{ url('purchase/budgetary_availability/{id}/edit') }}"
                    />
                </div>
            </div>
        </div>
    </div>
@stop
