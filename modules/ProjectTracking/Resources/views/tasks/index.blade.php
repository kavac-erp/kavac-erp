@extends('projecttracking::layouts.master')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    Seguimiento
@stop

@section('maproute-title')
    Tareas
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Tareas</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('projecttracking.tasks.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <project-tracking-tasks-list route_list="{{ url('projecttracking/tasks/show/vue-list') }}"
                        route_edit="{{ url('projecttracking/tasks/edit/{id}') }}"
                        route_delete="{{ url('projecttracking/tasks/delete') }}">
                    </project-tracking-tasks-list>
                </div>
            </div>
        </div>
    </div>
@stop
