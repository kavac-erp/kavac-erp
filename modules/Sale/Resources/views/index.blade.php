@section('modules-js')
    @parent
    {!! Html::script(mix('modules/sale/js/app.js'), [], Request::secure()) !!}
@endsection

@permission('sale.dashboard')
@endpermission