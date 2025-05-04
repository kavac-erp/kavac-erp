<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;

/**
 * @class NotificationSettingController
 * @brief Gestiona información para la configuración de notificaciones del sistema
 *
 * Controlador para gestionar configuración de notificaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class NotificationSettingController extends Controller
{
    /**
     * Listado de las configuraciones de notificaciones registradas
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function index()
    {
        //
    }

    /**
     * Muestra un formulario para el registro de configuración de notificaciones
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Registra una nueva configuración de notificaciones
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de una configuración de notificación
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     NotificationSetting    $notificationSetting    Objeto con los datos de la configuración de
     *                                                           notificación a mostrar
     *
     * @return void
     */
    public function show(NotificationSetting $notificationSetting)
    {
        //
    }

    /**
     * Muestra un formulario con información de una configuración de notificaciones a actualizar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     NotificationSetting    $notificationSetting    Objeto con información de la configuración a actualizar
     *
     * @return void
     */
    public function edit(NotificationSetting $notificationSetting)
    {
        //
    }

    /**
     * Actualiza información sobre una configuración de notificaciones
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request                $request                Objeto con información de la petición
     * @param     NotificationSetting    $notificationSetting    Objeto con información de la configuración a actualizar
     *
     * @return void
     */
    public function update(Request $request, NotificationSetting $notificationSetting)
    {
        //
    }

    /**
     * Elimina una configuración de notificaciones
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     NotificationSetting    $notificationSetting    Objeto con información de la configuración a eliminar
     *
     * @return void
     */
    public function destroy(NotificationSetting $notificationSetting)
    {
        //
    }
}
