@extends('layouts.app')

@section('maproute-icon')
    <i class="ion-lock-combination"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-lock-combination"></i>
@stop

@section('maproute-actual')
    {{ __('Cierre de ejercicio') }}
@stop

@section('maproute-title')
    {{ __('Cierre') }}
@stop

@section('content')
@permission('closefiscalyear.create')
    <close-fiscal-years
        id="helpAudit"
        help-file="{{ json_encode(get_json_resource('ui-guides/fiscal_year_search.json')) }}"
        route_list="{{ route('index') }}">
    </close-fiscal-years>
@endpermission
@permission('closefiscalyear.list')
    @if(Module::has('Accounting'))
        <close-fiscal-years-list
            route_list="{{ route('close-fiscal-year.registers.vue-list') }}"
            currency_id="{{ $currencyId }}"
            fiscal_year="{{ $fiscalYear }}"
            ref="closeFiscalYearsList">
        </close-fiscal-years-list>
    @endif
@endpermission
@stop
