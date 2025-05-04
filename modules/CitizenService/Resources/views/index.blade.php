@section('modules-js')
    @parent
    {!! Html::script(mix('modules/citizenservice/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('citizenservice.dashboard')
@endpermission
