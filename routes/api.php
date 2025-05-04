<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas para APIs
|--------------------------------------------------------------------------
|
| Aquí es donde puede registrar las rutas API para su aplicación. Estas
| estas rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| es asignado al grupo "api" middleware. ¡Disfruta construyendo tu API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
