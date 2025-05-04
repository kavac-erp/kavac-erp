<?php

/** Controladores para la gestión de mensajes del sistema */

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\SystemMail;
use Illuminate\Support\Facades\Mail;

/**
 * @class MessageController
 * @brief Gestiona los mensajes del sistema
 *
 * Controlador para gestionar mensajes de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class MessageController extends Controller
{
    /**
     * Ejecuta la acción que envía mensajes por correo electrónico
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function send(Request $request)
    {
        $emails = explode(",", $request->toEmail);
        if (is_array($emails)) {
            foreach ($emails as $email) {
                Mail::to($email)->send(new SystemMail($request->subject, $request->message, $request->from));
            }
        } else {
            Mail::to($emails)->send(new SystemMail($request->subject, $request->message, $request->from));
        }
        return response()->json(['result' => true], 200);
    }
}
