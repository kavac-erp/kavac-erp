@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Datos Financieros
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardPayrollEmploymentForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar los Datos Financieros
                        @include('buttons.help', [
                            'helpId' => 'payrollFinancial',
                            'helpSteps' => get_json_resource('ui-guides/proceedings/financial_form.json', 'payroll')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div id="helpPayrollFinancial">
                    @if(isset($payrollfinancial_edit))
                        <payroll-financial-form
                            route_list="{{ url('payroll/financials') }}"
                            :payrollfinancial_edit="{{ $payrollfinancial_edit }}">
                        </payroll-financial-form>
                    @else
                        <payroll-financial-form
                            route_list="{{ url('payroll/financials') }}">
                        </payroll-financial-form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
