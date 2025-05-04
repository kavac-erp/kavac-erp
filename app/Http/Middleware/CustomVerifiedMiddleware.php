<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

/**
 * @class CustomVerifiedMiddleware
 * @brief Middleware para gestionar la verificación de correo electrónico
 * cuando está configurado el acceso a través de active directory
 *
 * Gestiona la verificación de cuentas cuando está configurado el active directory
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CustomVerifiedMiddleware
{
    /**
     * Gestiona la petición
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $redirectToRoute = null)
    {
        if (!env('ACTIVE_DIRECTORY', false)) {
            if (
                ! $request->user() ||
                ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())
            ) {
                return $request->expectsJson()
                        ? abort(403, 'Tu cuenta de correo no está verificada.')
                        : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
            }
        }
        return $next($request);
    }
}
