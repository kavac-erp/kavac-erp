<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Notifications\SystemNotification;

/**
 * @class RequiredDocumentController
 * @brief Gestiona las notificaciones de la aplicación
 *
 * Controlador para gestionar las notificaciones de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SystemNotificationController extends Controller
{
    /**
     * Objeto o arreglo con información del o los usuarios a los cuales enviar una notificación
     *
     * @var object|array $toUser
     */
    protected $toUser;

    /**
     * Método constructor del controlador de notificaciones del sistema
     *
     * @param  object  $toUser Objeto con información del usuario al cual enviar una notificación
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct($toUser)
    {
        $this->toUser = $toUser;
    }

    /**
     * Método que envía notificaciones del sistema a usuarios
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request          $request    Objeto con información de la petición
     *
     * @return     JsonResponse   Json con información sobre el resultado del envío de la notificación
     */
    public function send(Request $request)
    {
        if (is_array($this->toUser)) {
            foreach ($this->toUser as $toUser) {
                $toUser->notify(new SystemNotification('Notificación del sistema', $request->details));
            }
        } else {
            $this->toUser->notify(new SystemNotification('Notificación del sistema', $request->details));
        }

        return response()->json(['result' => true, 'message' => 'Success'], 200);
    }
}
