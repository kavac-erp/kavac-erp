@section('modules-js')
    @parent
    {!! Html::script(mix('modules/technicalsupport/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('technicalsupport.dashboard')
@endpermission