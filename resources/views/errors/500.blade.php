@extends('layouts.app')

@section('custom-page')
    <div class="notfoundpanel">
        <h1>500!</h1>
        <h3>{{ __('¡Error interno del Servidor!') }}</h3>
        <p>{{ __('Se ha generado un error interno en el servidor.') }}</p>
        <button type="button" class="btn btn-sm bt-primary" onclick="window.history.back();">
            {{ __('Regresar') }}
        </button>
    </div>
@stop
