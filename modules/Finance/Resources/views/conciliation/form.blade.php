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
    {{ __('Conciliación') }}
@stop


@section('content')
    @role(['admin', 'finance'])
        <div class="row">
            <div class="col-12">
                <div class="card" id="cardConciliacionForm">
                    <div class="card-header">
                        <h6 class="card-title text-uppercase">
                            {{ __('Conciliación bancaria') }}
                            @include('buttons.help', [
                                'helpId' => 'bankMovements',
                                'helpSteps' => get_json_resource('ui-guides/bank/bank_movements.json', 'finance'),
                            ])
                        </h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    @if (isset($financeConciliation))
                        <finance-conciliacion-form route_list="{{ route('finance.conciliation.index') }}"
                            :conciliation='{{ $financeConciliation }}' />
                    @else
                        <finance-conciliacion-form route_list="{{ route('finance.conciliation.index') }}" />
                    @endif
                </div>
            </div>
        </div>
    @endrole
@stop
