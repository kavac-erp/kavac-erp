<?php

namespace App\Http\Controllers\Admin;

use App\Roles\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\System as SystemNotification;

/**
 * @class LogController
 * @brief Controlador de Eventos Log
 *
 * Clase que gestiona los eventos de log
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class LogController extends Controller
{
    /**
     * Método que genera eventos logs a partir de errores ocasionados en el FrontEnd
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse     JSON con el resultado del evento
     */
    public function frontEnd(Request $request)
    {
        // Vista que genera el error
        $view = $request->view;
        // Línea que genera el error
        $line = $request->line;
        // Mensaje o descripción del evento de error generado
        $msg  = $request->message;
        // Código de error generado
        $code = $request->code;
        // Tipo de error generado
        $errorType = $request->type;
        // URL que genera el error
        $url = $request->url;
        // Método de la petición (get|post|put|patch|delete)
        $method = $request->method;
        // Datos acerca de la traza de errores
        //$stacktrace = json_encode($request->r->config->data);
        // Nombre de la función que generó el log. Esta variable es opcional
        $function = (!is_null($request->func)) ? " en la función [{$request->func}]" : '';

        // Mensaje de error generado
        $errorMessage = __('Error generado por la vista [:view]', ['view' => $view]) . "\n";
        $errorMessage .= __('Código: :code', ['code' => $code]) . "\n";
        $errorMessage .= __('Tipo: :type', ['type' => $errorType]) . "\n";
        $errorMessage .= __('URL: :url', ['url' => $url]) . '\n';
        $errorMessage .= __('Método: :method', ['method' => $method]) . "\n";
        $errorMessage .= __('Mensaje: :msg', ['msg' => $msg]);

        Log::channel('front_end')->error($errorMessage);

        // Objeto con información del rol desarrollador
        $devRole = Role::where('slug', 'dev')->first();
        if ($devRole) {
            foreach ($devRole->users()->where('active', true)->get() as $user) {
                $user->notify(
                    new SystemNotification('Error en instrucción', $errorMessage, '')
                );
            }
        }

        return response()->json(['result' => true], 200);
    }
}
