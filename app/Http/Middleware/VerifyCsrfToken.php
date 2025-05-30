<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * @class VerifyCsrfToken
 * @brief Gestiona los middleware para los tokens CSRF
 *
 * Gestiona los middleware para los tokens CSRF
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * Indica si la cookie XSRF-TOKEN debe establecerse en la respuesta.
     *
     * @var bool $addHttpCookie
     */
    protected $addHttpCookie = true;

    /**
     * Las URL que deben excluirse de la verificación CSRF.
     *
     * @var array $except
     */
    protected $except = [
        'unlockscreen'
    ];
}
