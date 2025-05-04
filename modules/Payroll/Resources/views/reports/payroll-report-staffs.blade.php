@extends('payroll::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-folder-outline"></i>
@stop

@section('maproute-actual')
    Talento Humano
@stop

@section('maproute-title')
    Reportes
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Reporte de trabajadores</h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.minimize')
                    </div>
                </div>
                @php
                    $user = auth()->user();
                    $profileUser = $user->profile;
                    if ($profileUser && isset($profileUser->institution_id)) {
                        $institution_id = $profileUser->institution_id;
                    } else {
                        $institution_id = \App\Models\Institution::where('default', true)->first()->id;
                    }
                @endphp
                <payroll-report-staffs institution_id= "{{ $institution_id }}">
                </payroll-report-staffs>
            </div>
        </div>
    </div>
@stop
