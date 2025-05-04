@extends('payroll::layouts.master')

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
                    <payroll-availability-form
                        :payroll="{{ $payroll }}"
                        :budgetaccounts="{{ $budgetAccounts }}"
                        :totalamount="{{ $totalAmount }}"
                    />
                </div>
            </div>
        </div>
    @stop
@endif
