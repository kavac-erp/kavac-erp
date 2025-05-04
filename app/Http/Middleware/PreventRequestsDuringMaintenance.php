<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

/**
 * @class PreventRequestsDuringMaintenance
 * @brief Gestiona los middleware para prevenir las peticiones durante el modo en mantenimiento de la aplicación
 *
 * Gestiona los middleware para prevenir las peticiones durante el modo en mantenimiento de la aplicación
 */
class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * Las URL que deben ser accesibles mientras el modo de mantenimiento está habilitado.
     *
     * @var array $except
     */
    protected $except = [
        //
    ];
}
