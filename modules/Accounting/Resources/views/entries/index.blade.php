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
            <div id="helpSearchEntriesForm" class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        Buscador de asientos contables
                        @include('buttons.help', [
                            'helpId' => 'AccountingEntries',
                            'helpSteps' => get_json_resource('ui-guides/entries/search_entries.json', 'accounting')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('accounting.entries.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <accounting-entry
                        :categories="{{ $categories }}" :institutions="{{ $institutions }}"
                        year_old="{{ $yearOld }}" route_edit="{{ url('accounting/entries/{id}/edit') }}"
                        institution_id_prop="{!! (isset($institution)) ? $institution->id : 'null' !!}"
                    />
                </div>
            </div>
        </div>

        @if(@Auth::user()->hasRole('admin') || @Auth::user()->hasRole('account'))
            <div class="col-12">
                <div class="card" id="helpSearchEntriesApproved">
                    <div class="card-header">
                        <h6 class="card-title">Listado de asientos contables</h6>
                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                    </div>
                    <div class="card-body">
                        <accounting-entry-list-approved
                            route_edit="{{ url('accounting/entries/{id}/edit') }}"
                        ></accounting-entry-list-approved>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop
