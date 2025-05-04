<?php

namespace App\Observers;

use App\Notifications\SystemNotification;
use App\Models\NotificationSetting;

/**
 * @class ModelObserver
 * @brief Observa los eventos de los modelos
 *
 * Gestiona los observadores de eventos en los modelos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ModelObserver
{
    /**
     * Gestiona el evento "created" de un modelo
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      object           $model    Objeto con información del modelo que genera el evento
     *
     * @return void
     */
    public function created($model)
    {
        $this->setNotifications($model, 'created');
    }

    /**
     * Gestiona el evento "updated" de un modelo
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      object           $model    Objeto con información del modelo que genera el evento
     *
     * @return void
     */
    public function updated($model)
    {
        $this->setNotifications($model, 'updated');
    }

    /**
     * Gestiona el evento "deleted" de un modelo
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      object           $model    Objeto con información del modelo que genera el evento
     *
     * @return void
     */
    public function deleted($model)
    {
        $this->setNotifications($model, 'deleted');
    }

    /**
     * Gestiona el evento "forceDeleted" de un modelo
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      object           $model    Objeto con información del modelo que genera el evento
     *
     * @return void
     */
    public function forceDeleted($model)
    {
        $this->setNotifications($model, 'forceDeleted');
    }

    /**
     * Establece las notificaciones a enviar de acuerdo a la configuración del sistema
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      object              $model    Objeto con información del modelo que genera el evento observado
     * @param      string              $type     Tipo de evento generado. created, updated, deleted o forceDeleted
     *
     * @return void
     */
    public function setNotifications($model, $type)
    {
        // Obtiene la clase de un modelo
        $modelClass = get_class($model);

        // Obtiene la configuración del modelo del cual enviar una notificación
        $notifySetting = NotificationSetting::where('model', $modelClass)->first();


        if ($notifySetting) {
            // Nombre del evento o modelo a notificar
            $eventName = $notifySetting->name;
            // Tipo de evento a notificar. creado, actualizado, eliminado o eliminado permanentemente
            $eventType = '';
            // Descripción corta del evento a notificar
            $event = '';

            switch ($type) {
                case 'created':
                    $eventType = __('creado(a)');
                    $event = __('un registro');
                    break;
                case 'updated':
                    $eventType = __('actualizado(a)');
                    $event = __("una actualización");
                    break;
                case 'deleted':
                    $eventType = __('eliminado(a)');
                    $event = __("una eliminación");
                    break;
                case 'forceDeleted':
                    $eventType = __('eliminado(a) permanentemente');
                    $event = __("una eliminación permanente");
                    break;
            }

            if (!empty($eventType) && !empty($event)) {
                // Título de la notificación a enviar
                $title = "{$eventName} {$eventType}";
                // Detalle o descripción de la notificación a enviar
                $details = "Se realizó {$event} de datos en {$eventName}";

                foreach ($notifySetting->users()->get() as $user) {
                    // Notificación al usuario configurado para recibir notificaciones del sistema
                    $user->notify(new SystemNotification($title, $details));
                }
            }
        }
    }
}
