@extends('layouts.app')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Cierre de ejercicio') }}
@stop

@section('maproute-title')
    {{ __('Asientos  contables') }}
@stop

@section('modules-js')
    @if(Module::has('Accounting'))
        @permission('closefiscalyear.entries')
            {!! Html::script(mix('modules/accounting/js/shared.js'), [], Request::secure()) !!}
        @endpermission
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="helpEntriesForm">
                <div class="card-header">
                    <h6 class="card-title">
                        Gestión de asientos de ajustes
                        @include('buttons.help', [
                            'helpId' => 'AccountingSearchEntries',
                            'helpSteps' => get_json_resource('ui-guides/entries/form_entries.json', 'accounting')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => route('accounting.entries.index')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <accounting-entry-form :close_fiscal_year="true" :categories="{{ $categories }}" :institutions="{{ $institutions }}" :currencies="{{ $currencies }}" />
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Asiento contable</h6>
                    <div class="card-btns">
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <accounting-entry-form-account :close_fiscal_year="true" :accounting_accounts="{{ $AccountingAccounts ?? json_encode([]) }}"/>
                </div>
            </div>
        </div>
    </div>
@stop