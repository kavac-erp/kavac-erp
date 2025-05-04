<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * @class System
 * @brief Notificaciones del sistema
 *
 * Gestiona las Notificaciones del sistema
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class System extends Notification //implements ShouldQueue
{
    use Queueable;

    /**
     * Título de la notificación
     *
     * @var string $title
     */
    protected $title;

    /**
     * Módulo que genera la notificación
     *
     * @var string $module
     */
    protected $module;

    /**
     * Descripción de la notificación
     *
     * @var string $description
     */
    protected $description;

    /**
     * Determina si se notifica a través de correo o a través de notificaciones en tiempo real en la aplicación
     *
     * @var bool $notifyToEmail
     */
    protected $notifyToEmail;

    /**
     * Archivos adjuntos a enviar en el correo a notificar
     *
     * @var array $filesToEmail
     */
    protected array $filesToEmail;

    /**
     * Crea una nueva instancia de la notificación
     *
     * @param  string  $title
     * @param  string  $module
     * @param  string  $description
     * @param  bool    $notifyToEmail
     * @param  array   $filesToEmail
     *
     * @return void
     */
    public function __construct($title, $module, $description, $notifyToEmail = false, $filesToEmail = [])
    {
        $this->title = $title;
        $this->module = $module;
        $this->description = $description;
        $this->notifyToEmail = $notifyToEmail;
        $this->filesToEmail = $filesToEmail;
    }

    /**
     * Obtiene los canales de notificación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return $this->notifyToEmail ? ['mail'] : ['database', 'broadcast'];
    }

    /**
     * Gestiona el envío de la notificación por correo
     *
     * @param  mixed  $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email = (new MailMessage())
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject($this->title)
            ->line($this->title)
            ->line($this->description);

        foreach ($this->filesToEmail as $key => $file) {
            $email->attachData($file['file'], $file['fileName'] ?? $key . '.xlsx');
        }

        return $email;
    }

    /**
     * Gestiona la notificación en la aplicación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'module' => $this->module,
            'description' => $this->description,
        ];
    }

    /**
     * Gestiona la notificación en base de datos
     *
     * @param   mixed $notifiable
     *
     * @return  array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'module' => $this->module,
            'description' => $this->description,
        ];
    }

    /**
     * Gestiona la notificación en tiempo real
     *
     * @param   mixed $notifiable
     *
     * @return  \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'module' => $this->module ?? '',
            'description' => $this->description,
        ]);
    }
}
