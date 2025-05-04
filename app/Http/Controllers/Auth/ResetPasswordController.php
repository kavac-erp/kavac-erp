<?php

/** Controladores para la gestión de autenticación de usuarios */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * @class ResetPasswordController
 * @brief Gestiona el reinicio de contraseñas
 *
 * Controlador para gestionar el reinicio de contraseñas de usuario
 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */
    use ResetsPasswords;

    /**
     * Ruta a la cual redireccionar al usuario después de haber reiniciado su contraseña.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Crea una nueva instancia del controlador.
     *
     * @method  __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
