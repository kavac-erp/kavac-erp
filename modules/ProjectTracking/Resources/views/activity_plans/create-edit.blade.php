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
            <div class="card" id="cardProjectTrackingActivityPlanForm">
                <div class="card-header">
                    <h6 class="card-title">Registrar el plan de actividad
                        @include('buttons.help', [
                            'helpId' => 'ProjectTrackingActivityPlanForm',
                            //'helpSteps' => get_json_resource('ui-guides/proceedings/activityplan_form.json', 'projecttracking')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                <project-tracking-activity-plan 
                    route_list="{{ url('projecttracking/activity_plans') }}"
                    :activity_planid ="{!! (isset($projecttrackingActivityPlan)) ? $projecttrackingActivityPlan->id : 'null' !!}"
                    :payroll_employer_id="{!! (isset($payrollStaff)) ? $payrollStaff : 'null' !!}">
                </project-tracking-activity-plan>
             </div>
        </div>
    </div>
@stop