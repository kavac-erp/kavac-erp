@section('modules-js')
    @parent
    {!! Html::script(mix('modules/digitalsignature/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('digitalsignature.dashboard')
@endpermission