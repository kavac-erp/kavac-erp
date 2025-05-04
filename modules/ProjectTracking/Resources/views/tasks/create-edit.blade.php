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
            <div class="card" id="cardProjectTrackingTaskForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar nueva tarea
                        @include('buttons.help', [
                            'helpId' => 'ProjectTrackingTaskForm',
                            //'helpSteps' => get_json_resource('ui-guides/proceedings/tasks_form.json', 'projecttracking')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <project-tracking-tasks route_list="{{ url('projecttracking/tasks') }}" :task_id="{!! isset($projecttrackingTask) ? $projecttrackingTask->id : 'null' !!}">
                </project-tracking-tasks>
            </div>
        </div>
    </div>
@stop
