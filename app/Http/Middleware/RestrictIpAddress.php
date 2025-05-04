<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Parameter;

/**
 * @class RestrictIpAddress
 * @brief Restringe el acceso a la aplicación según dirección IP
 *
 * Restringe el acceso a la aplicación según dirección IP
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RestrictIpAddress
{
    /**
     * Gestiona las peticiones
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $blockedListIp = Parameter::where(['p_key' => 'black_list_ip'])->first();
        if ($blockedListIp && in_array($request->ip(), json_decode($blockedListIp->p_value))) {
            abort(403);
        }
        return $next($request);
    }
}
