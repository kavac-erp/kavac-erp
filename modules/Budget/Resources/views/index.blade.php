@section('modules-js')
    @parent
    {!! Html::script(mix('modules/budget/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('budget.dashboard')
@endpermission