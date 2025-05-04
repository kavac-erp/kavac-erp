<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

/**
 * @class ResetPasswordNotification
 * @brief Notificaciones para el reestablecimiento de contraseña
 *
 * Gestiona las Notificaciones para el reestablecimiento de contraseña
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    /**
     * Envía notificación al usuario con el enlace para el reestablecimiento de la contraseña
     *
     * @param  string  $url
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(config('app.name') . ' - ' . __('Solicitud para reinicio de contraseña'))
            ->line(
                __('Estás recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de
                contraseña para tu cuenta.')
            )->action(__('Reestablecer contraseña'), $url)
            ->line(
                __('Este enlace, para el reestablecimiento de su contraseña, caducará en ') .
                config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . __(' minutos.')
            )->line(__('Si no solicitó un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.'));
    }
}
