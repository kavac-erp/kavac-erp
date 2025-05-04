<?php

/** Notificaciones de la aplicación */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @class VerifyEmailNotification
 * @brief Notificaciones para la verificación de usuarios
 *
 * Gestiona las Notificaciones para la verificación de usuarios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @method  buildMailMessage
     *
     * @param  string  $url
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(config('app.name') . ' - ' . __('Verificar usuario'))
            ->line(__('Haga clic en el botón de abajo para verificar su usuario.'))
            ->action(__('Verificar usuario'), $url)
            ->line(__('Omita este mensaje si no le fue asignada una cuenta en el sistema.'));
    }
}
