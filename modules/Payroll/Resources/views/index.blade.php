@section('modules-js')
    @parent
    {!! Html::script(mix('modules/payroll/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('payroll.dashboard')
@endpermission