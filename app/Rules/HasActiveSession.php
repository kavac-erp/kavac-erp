<?php

namespace App\Rules;

use App\Models\User;
use App\Models\Session;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

/**
 * @class HasActiveSession
 * @brief Reglas de validación
 *
 * Gestiona las reglas de validación que verifica si el usuario tiene una sesión activa
 *
 * @author Ing. Roldan Vargas <roldandvg@cenditel.gob.ve> | <rvargas@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class HasActiveSession implements Rule
{
    /**
     * Crea una nueva instancia de la regla de validación
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determina si la regla de validación pasa la verificación.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $request = Request::capture();
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        $user = User::where('username', $value)->first();

        if (!$user) {
            return true;
        }
        $session = Session::where('user_id', $user->id)->where('ip_address', '<>', $ip)->first();
        if ($session) {
            return false;
        }
        return true;
    }

    /**
     * Obtiene el mensaje de error de la regla de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Su usuario ya tiene una sesión activa en la aplicación. Para iniciar sesión en este equipo debe cerrar la sesión activa.';
    }
}
