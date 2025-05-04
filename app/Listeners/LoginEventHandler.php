<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Redis;

/**
 * @class LoginEventHandler
 * @brief Gestiona eventos en la autenticación de usuarios
 *
 * Gestiona los eventos generados en la autenticación de usuarios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class LoginEventHandler
{
    /**
     * Constructor de la clase que crea los eventos a ser monitoreados
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Registra los eventos en la autenticación de usuarios
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  object  $event
     *
     * @return void
     */
    public function handle($event)
    {
        $event->user->last_login = date('Y-m-d H:i:s');
        $event->user->save();
        if (config('session.driver') === 'redis') {
            $userData = [
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'last_activity' => $event->user->last_login
            ];
            $jsonUserData = json_encode($userData);
            Redis::set('session_user:' . $event->user->id, $jsonUserData);
        }
    }
}
