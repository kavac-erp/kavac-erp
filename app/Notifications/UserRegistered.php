<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

/**
 * @class UserRegistered
 * @brief Notificaciones de usuario registrado
 *
 * Gestiona las Notificaciones de usuario registrado
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Objeto con información del usuario registrado
     *
     * @var User $user
     */
    public $user;

    /**
     * Contraseña generada del usuario registrado
     *
     * @var string $password
     */
    public $password;

    /**
     * Título de la notificación
     *
     * @var string $notifyTitle
     */
    public $notifyTitle;

    /**
     * Mensaje de la notificación
     *
     * @var string $notifyMessage
     */
    public $notifyMessage;

    /**
     * Indica si el usuario esta registrado y la notificación se debe a la generación de nuevas credenciales de acceso
     *
     * @var boolean $isRegistered
     */
    public $isRegistered;

    /**
     * Número máximo de intentos
     *
     * @var integer $tries
     */
    public $tries = 5;

    /**
     * Tiempo máximo de espera en segundos
     *
     * @var integer $timeout
     */
    public $timeout = 300;

    /**
     * Crea una nueva instancia de la notificación.
     *
     * @return void
     */
    public function __construct(User $user, $password, $isRegistered = false)
    {
        $this->user = $user;
        $this->password = $password;
        $this->notifyTitle = __('Modificar contraseña');
        $this->notifyMessage = __('Bienvenido al sistema, recuerde modificar su contraseña en el primer acceso');
        $this->isRegistered = $isRegistered;
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
        return ['mail', 'database'];
    }

    /**
     * Gestiona la notificación por correo
     *
     * @method  toMail
     *
     * @param  mixed  $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');
        $subject = config('app.name') . ' - ' . __('Registro de usuario');
        $greeting = __('Bienvenido, :username', ['username' => $this->user->name]);
        $message = (!$this->isRegistered)
                    ? __('Se ha registrado un usuario en la plataforma con las siguientes credenciales de acceso:')
                    : __('Se han generado nuevas credenciales de acceso las cuales se indican a continuación:');
        if (config('auth.active_directory.enabled', false)) {
            $message = __('Se ha registrado un usuario en la plataforma ERP KAVAC con las credenciales de acceso del Directorio Activo de su organización');
        }
        $mailMessage = (new MailMessage())
                            ->from($fromEmail, $fromName)
                            ->subject($subject)
                            ->greeting($greeting)
                            ->line($message);
        if (!config('auth.active_directory.enabled', false)) {
            // Si el método de autenticación es a través de la configuración por defecto, se le envía las credenciales al usuario
            $mailMessage->line(__('**Usuario:** :username', ['username' => $this->user->username]))
                        ->line(__('**Contraseña:** :password', ['password' => $this->password]));
        }
        $mailMessage->line(__('Para acceder pulse sobre el botón a continuación'))
                    ->action(__('Acceso'), route('index'))
                    ->line(__(
                        'Este correo es enviado de manera automática por la aplicación y no esta siendo monitoreado. ' .
                        'Por favor no responda a este correo!'
                    ));
        return $mailMessage;
    }

    /**
     * Gestiona la notificación de la aplicación
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->notifyTitle,
            'module' => null,
            'message' => $this->notifyMessage,
        ];
    }

    /**
     * Gestiona la notificación de la base de datos
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->notifyTitle,
            'module' => null,
            'message' => $this->notifyMessage,
        ];
    }
}
