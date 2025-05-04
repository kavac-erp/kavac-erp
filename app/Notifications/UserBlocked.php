<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

/**
 * @class UserBlocked
 * @brief Notificaciones de usuario bloqueado
 *
 * Gestiona las Notificaciones de usuario bloqueado
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UserBlocked extends Notification
{
    use Queueable;

    /**
     * Objeto con información del usuario bloqueado
     *
     * @var User $user
     */
    public $user;

    /**
     * IP desde donde se produjo el bloqueo
     *
     * @var string $ip
     */
    public $ip;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @return void
     */
    public function __construct(User $user, $ip = '')
    {
        $this->user = $user;
        $this->ip = $ip;
    }

    /**
     * Gestiona el mecanismo de notificación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Obtiene la representación por correo electrónico de la notificación.
     *
     * @param  mixed  $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject(config('app.name') . ' - ' . __('Usuario bloqueado'))
                    ->greeting(__('Advertencia, :username', ['username' => $this->user->name]))
                    ->line(
                        __('Usted a intentado ingresar al sistema demasiadas veces con datos incorrectos. ' .
                           'Por medidas de seguridad su usuario fue bloqueado, contacte a soporte para ayuda.')
                    )
                    ->line(__(
                        'Este correo es enviado de manera automática por la aplicación y no esta siendo monitoreado. ' .
                        'Por favor no responda a este correo!'
                    ));
    }
}
