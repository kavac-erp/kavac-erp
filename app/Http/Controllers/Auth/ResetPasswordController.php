<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * @class ResetPasswordController
 * @brief Gestiona el reinicio de contraseñas
 *
 * Controlador para gestionar el reinicio de contraseñas de usuario
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Ruta a la cual redireccionar al usuario después de haber reiniciado su contraseña.
     *
     * @var string $redirectTo
     */
    protected $redirectTo = '/';

    /**
     * Crea una nueva instancia del controlador.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
