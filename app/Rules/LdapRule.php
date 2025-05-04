<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

/**
 * @class LdapRule
 * @brief Reglas de validación
 *
 * Gestiona las reglas de validación para el acceso a través de Directorio Activo
 *
 * @author Ing. Roldan Vargas <roldandvg@cenditel.gob.ve> | <rvargas@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class LdapRule implements Rule
{
    /**
     * Mensaje de error
     *
     * @var string $errorMessage
     */
    protected $errorMessage;

    /**
     * Crea una nueva instancia de la regla de validación
     *
     * @return void
     */
    public function __construct()
    {
        $this->errorMessage = 'El usuario no esta autorizado para autenticarse en la aplicación';
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
        if (!config('auth.active_directory.enabled', false)) {
            /* Si no se ha definido la autenticación a través de directorio activo */
            return true;
        }
        $username = $value;
        $ds = ldap_connect(config('auth.active_directory.url'));
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

        try {
            $bind = ldap_bind($ds, $username . config('auth.active_directory.dn'), request()->password);
        } catch (\Throwable $th) {
            /* Excepción que se ejecuta si la autenticación con el Directorio Activo falla */
            $this->errorMessage = 'Las credenciales de acceso suministradas no son válidas';
            return false;
        }

        /* Condición que evalua si el usuario existe */
        if ($bind) {
            $search = ldap_search($ds, config('auth.active_directory.base_dn'), "uid=$username");
            $entry = ldap_first_entry($ds, $search);
            $attributes = ldap_get_attributes($ds, $entry);
            $userEmail = strtolower($attributes['mail'][0]);

            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                /*
                 * Si el usuario no está registrado dentro de la aplicación se denega el acceso aún cuando exista en
                 * el Directorio Activo para evitar acceso no autorizado dentro del sistema
                 */
                $this->errorMessage = 'El usuario no se encuentra registrado en esta aplicación. Acceso denegado.';
                return false;
            }

            if (!Hash::check(request()->password, $user->password)) {
                /* Si la contraseña es distina a la configurada en el directorio activo la actualiza en la aplicación */
                $user->update([
                    'password' => Hash::make(request()->password),
                ]);
            }
            if ($user->email_verified_at === null) {
                /* Verifica, por defecto, al usuario dentro de la aplicación */
                $user->update([
                    'email_verified_at' => Carbon::now(),
                ]);
            }

            return true;
        }
        return false;
    }

    /**
     * Obtiene el mensaje de error de la regla de validación.
     *
     * @return string
     */
    public function message()
    {
        if (!config('auth.active_directory.enabled', false)) {
            return 'Error validando el usuario';
        }
        return $this->errorMessage;
    }
}
