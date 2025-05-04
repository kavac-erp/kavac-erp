@extends('layouts.app')

@section('custom-page')
    <div class="notfoundpanel">
        <h1>410!</h1>
        <h3>{{ __('¡Enlace inválido!') }}</h3>
        <p>
            {{ __('El enlace suministrado es inválido o ha expirado, solicite un nuevo enlace.') }}
        </p>
        <a href="{{ route('index') }}" class="btn btn-sm bt-primary">{{ __('Regresar') }}</a>
    </div>
@stop
