@extends('accounting::layouts.master')

@section('maproute-icon')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-arrow-graph-up-right"></i>
@stop

@section('maproute-actual')
    Contabilidad
@stop

@section('maproute-title')
    Asientos contables
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div id="helpEntriesForm" class="card">
            <div class="card-header">
                <h6 class="card-title">
                    Gestión de asientos contables
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
                @if(!isset($entry))
                    <accounting-entry-form
                        route_list="{{ url('accounting//entries') }}"
                        :categories="{{ $categories }}" :institutions="{{ $institutions }}"
                        institution_id_prop="{!! (isset($institution)) ? $institution->id : 'null' !!}"
                    />
                @else
                    <accounting-entry-form
                        route_list="{{ url('accounting//entries') }}"
                        :categories="{{ $categories }}" :institutions="{{ $institutions }}"
                        :data_edit="{{ $data_edit }}"
                        institution_id_prop="{!! (isset($institution)) ? $institution->id : 'null' !!}"
                    />
                @endif
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
                @if(!isset($entry))
                    <accounting-entry-form-account
                        :accounting_accounts="{{ $AccountingAccounts ?? json_encode([]) }}"
                    />
                @else
                    <accounting-entry-form-account
                        :accounting_accounts="{{ $AccountingAccounts ?? json_encode([]) }}"
                        :entries="{{ $entry }}"
                        route_list="{{ url('accounting/entries') }}"
                    />
                @endif
            </div>
        </div>
    </div>
</div>
@stop
