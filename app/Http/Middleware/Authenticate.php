<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

/**
 * @class Authenticate
 * @brief Gestiona los middleware de autenticación
 *
 * Gestiona los middleware de autenticación
 */
class Authenticate extends Middleware
{
    /**
     * Obtiene la ruta a la que se debe redirigir al usuario cuando no está autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }

        return response()->json(['result' => false], 200);
    }
}
