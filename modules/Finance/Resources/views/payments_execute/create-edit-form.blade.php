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
	{{ __('Emisiones de Pago') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Emisión de Pago') }}
                        @include('buttons.help', [
                        'helpId' => 'FinanceExcecute',
                        'helpSteps' => get_json_resource('ui-guides/pay/pay_execute.json', 'finance')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <finance-payment-execute
                    route_list="{{ route('finance.payment-execute.index') }}"
                    edit_object="{{ (isset($paymentExecute)) ? $paymentExecute : '' }}"
                    registered_accounts="{{ (isset($registeredAccounts)) ? $registeredAccounts : '' }}"
                />
            </div>
        </div>
    </div>
@endsection