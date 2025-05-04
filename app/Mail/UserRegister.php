<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @class UserRegister
 * @brief Gestiona los correos de notificación de usuarios registrados
 *
 * Gestiona los correos de notificación de usuarios registrados
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UserRegister extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Objeto con información del usuario
     *
     * @var User $user
     */
    public $user;

    /**
     * Contraseña de acceso generada por el sistema
     *
     * @var string $password
     */
    public $password;

    /**
     * Nombre de la aplicación
     *
     * @var string $appName
     */
    public $appName;

    /**
     * URL de la aplicación
     *
     * @var string $appUrl
     */
    public $appUrl;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @return void
     */
    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
        $this->appName = config('app.name');
        $this->appUrl = config('app.url');
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('app.name') . " - Registro")->markdown('emails.user-register');
    }
}
