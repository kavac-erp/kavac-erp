@extends('layouts.app')

@section('modules-js')
    @parent
    {!! Html::script(mix('modules/projecttracking/js/app.js'), [], Request::secure()) !!}
@endsection

@section('module-css')
    @parent
    {!! Html::style(mix('modules/projecttracking/css/app.css'), [], Request::secure()) !!}
@endsection


