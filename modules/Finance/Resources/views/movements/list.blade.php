@extends('finance::layouts.master')

@section('maproute-icon')
    <i class="mdi mdi-ballot-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="mdi mdi-ballot-outline"></i>
@stop

@section('maproute-actual')
    {{ __('Finanzas') }}
@stop

@section('maproute-title')
    {{ __('Movimientos bancarios') }}
@stop

@section('content')
    @role(['admin','finance'])
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Listado de Movimientos bancarios</h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.new', ['route' => route('finance.movements.create')])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <div class="card-body">
                        <finance-bank-movements-list
                            route_edit="{{ url('finance/movements/edit') }}"
                            route_delete="{{ url('finance/movements') }}">
                        </finance-bank-movements-list>
                    </div>
                </div>
            </div>
        </div>
    @endrole
@stop