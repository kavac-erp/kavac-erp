<?php

namespace App\Notifications;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * @class SystemNotification
 * @brief Notificaciones del sistema
 *
 * Gestiona las Notificaciones del sistema
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Título de la notificación
     *
     * @var string $title
     */
    public $title;

    /**
     * Detalles de la notificación
     *
     * @var string $details
     */
    public $details;

    /**
     * Fecha y hora en que se realiza la notificación
     *
     * @var DateTime $currentTimestamp
     */
    public $currentTimestamp;

    /**
     * Crea una nueva instancia de la notificación
     *
     * @return void
     */
    public function __construct($title, $details, $currentTimestamp = null)
    {
        $this->title = $title;
        $this->details = $details;
        $this->currentTimestamp = $currentTimestamp ?? \Carbon\Carbon::now()->toDateString();
    }

    /**
     * Establece el mecanismo de notificación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Gestiona los datos de la notificación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->details,
            'currentTimestamp' => $this->currentTimestamp
        ];
    }

    /**
     * Gestiona los datos de la notificación a registrar en base de datos
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->details,
            'currentTimestamp' => $this->currentTimestamp
        ];
    }

    /**
     * Gestiona la notificación a enviar a la aplicación
     *
     * @param     mixed  $notifiable
     *
     * @return    BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->details,
            'currentTimestamp' => $this->currentTimestamp
        ]);
    }
}
