@extends('asset::layouts.master')

@section('maproute-icon')
    <i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-ios-pricetags-outline"></i>
@stop

@section('maproute-actual')
    Bienes
@stop

@section('maproute-title')
    Gestión de Bienes
@stop

@section('content')
    <asset-register
        id="RegisterForm"
        help-file="{{ json_encode(get_json_resource('ui-guides/registers/register_form.json', 'asset')) }}"
        route_list="{{ route('asset.register.index') }}"
        :parameters="{{ json_encode($parameters) }}"
        :assetid="{!! (isset($asset)) ? $asset->id : 'null' !!}"
        institution_id="{!! (isset($institution)) ? $institution->id : 'null' !!}"
    >
    </asset-register>
@stop
