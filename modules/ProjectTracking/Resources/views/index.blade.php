@section('modules-js')
    @parent
    {!! Html::script(mix('modules/projecttracking/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('projecttracking.dashboard')
@endpermission
