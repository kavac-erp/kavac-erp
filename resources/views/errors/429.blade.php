@extends('layouts.app')

@section('custom-page')
    <div class="notfoundpanel">
        <h1>429!</h1>
        <h3>{{ __('¡Límite de peticiones excedidas!') }}</h3>
        <p>{{ __('Se ha excedido en el límite de peticiones permitidas.') }}</p>
        <button type="button" class="btn btn-sm bt-primary" onclick="window.history.back();">
            {{ __('Regresar') }}
        </button>
    </div>
@stop
