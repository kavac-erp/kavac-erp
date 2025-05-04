@extends('layouts.app')

@section('modules-js')
    @parent
    {!! Html::script(mix('modules/finance/js/app.js'), [], Request::secure()) !!}
    @if(Module::has('Accounting'))
        {!! Html::script(mix('modules/accounting/js/shared.js'), [], Request::secure()) !!}
    @endif
@endsection

@section('modules-css')
    @parent
    {!! Html::style(mix('modules/finance/css/app.css'), [], Request::secure()) !!}
@endsection
