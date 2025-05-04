@extends('projecttracking::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-circuit"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-circuit"></i>
@stop

@section('maproute-actual')
    Seguimiento
@stop

@section('maproute-title')
    Plan de actividades
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Listado de plan de actividades</h6>
                    <hr>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('projecttracking.activity_plans.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <project-tracking-activity-plan-list route_list="{{ url('projecttracking/activity_plans/show/vue-list') }}"
                                          route_delete="{{ url('projecttracking/activity_plans/delete') }}"
                                          route_edit="{{ url('projecttracking/activity_plans/{id}/edit') }}"
                                          route_show="{{ url('projecttracking/activity_plans/{id}') }}">
                    </project-tracking-activity-plan-list>
                </div>
            </div>
        </div>
    </div>
@stop
