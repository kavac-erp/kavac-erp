@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion ion-ios-calendar-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion ion-ios-calendar-outline"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Esquema de Guardias
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Esquema de guardias</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('payroll.guard-schemes.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <payroll-guard-scheme-list
                        route_delete="{{ url('payroll/guard-schemes') }}"
                        route_edit="{{ url('payroll/guard-schemes/{id}/edit') }}"
                        route_show="{{ url('payroll/guard-schemes/show/{id}') }}"
                        route_list="{{ url('payroll/guard-schemes/vue-list') }}"
                        approve_permission="{{ auth()->user()->hasPermission('payroll.guard.scheme.approve') }}"
                        confirm_permission="{{ auth()->user()->hasPermission('payroll.guard.scheme.confirm') }}"
                        index_permission="{{ auth()->user()->hasPermission('payroll.guard.scheme.index') }}"
                        request_review_permission="{{ auth()->user()->hasPermission('payroll.guard.scheme.request.review') }}">
                    </payroll-guard-scheme-list>
                </div>
            </div>
        </div>
    </div>
@stop