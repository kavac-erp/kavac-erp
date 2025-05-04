<?php

/** Controladores para la gestión de autenticación de usuarios */

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Rules\LdapRule;
use Illuminate\Http\Request;
use App\Rules\HasActiveSession;
use Mews\Captcha\Facades\Captcha;
use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * @class LoginController
 * @brief Gestiona información de autenticación
 *
 * Controlador para gestionar la autenticación de usuarios
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;

    /**
     * Intentos fallidos restantes
     *
     * @var    integer
     */
    protected $remainAttempts;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Número máximo de intentos fallidos al tratar de autenticarse en la aplicación
     *
     * @var    integer
     */
    protected $maxAttempts = 3;

    /**
     * Tiempo establecido para volver a intentar el acceso al sistema después de varios intentos fallidos
     *
     * @var    integer
     */
    protected $decayMinutes = 2;

    /**
     * Crea una nueva instancia del controlador.
     *
     * @method  __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $this->remainAttempts = $this->maxAttempts - $this->limiter()->attempts($this->throttleKey(request()));
    }

    /**
     * Muestra el formulario de autenticación de usuario
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view(env('AUTH_VIEW', 'auth.login'));
    }

    /**
     * Gestiona una petición de acceso a la aplicación.
     *
     * @method  login
     *
     * @param  Request  $request
     *
     * @return RedirectResponse|Response|JsonResponse
     *
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        /** @var User Objeto con información del usuario a autenticar */
        $user = User::where('username', $request->username)->firstOrFail();

        if ($user !== null) {
            if (!is_null($user->blocked_at)) {
                return $this->sendLockedAccountResponse($request);
            } elseif (!$user->active) {
                return $this->sendInactiveAccountRequest($request);
            } elseif ($request->ip() !== '127.0.0.1' && Session::where('user_id', $user->id)->where('ip_address', '<>', $request->ip())->first()) {
                return $this->sendHasActiveSessionRequest($request);
            }
        }

        if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
            /** elimina la cantidad de intentos fallidos del usuario y se procede al bloqueo del mismo */
            $this->clearLoginAttempts($request);

            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Obtiene la instancia de peticiones de acceso fallidas.
     *
     * @method sendFailedLoginResponse
     *
     * @param  Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed', ['attempts' => $this->remainAttempts])],
        ]);
    }

    /**
     * Valida la petición de acceso del usuario.
     *
     * @method  validateLogin
     *
     * @param  Request  $request
     *
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => ['required', 'exists:users', new LdapRule],
            'password' => ['required', 'string'],
        ];
        if (!env('TEST_UNIT', false)) {
            $rules['captcha'] = ['required', 'captcha'];
        }
        $validateMessages = [
            $this->username() . '.required' => 'El nombre de usuario es obligatorio.',
            $this->username() . '.exists' => 'Estas credenciales no coinciden con nuestros registros',
            'password.required' => 'La contraseña es obligatoria.'
        ];
        $this->validate($request, $rules, $validateMessages);
    }

    /**
     * Obtiene el campo usado como nombre de usuario para el acceso a la aplicación, usado por el controlador.
     *
     * @method  username
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * El usuario ha sido autenticado en la aplicación.
     *
     * @method  authenticated
     *
     * @param  Request  $request
     * @param  mixed  $user
     *
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $user->lock_screen = false;
        $user->save();
    }

    /**
     * Actualiza la imagen del captcha
     *
     * @method    refreshCaptcha
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    object            Objeto con los datos de la nueva imagen generada
     */
    public function refreshCaptcha()
    {
        return Captcha::Img();
    }

    /**
     * Obtiene la instancia de la petición del usuario bloqueado.
     *
     * @method  sendLockedAccountResponse
     *
     * @param Request  $request
     *
     * @return Response
     */
    protected function sendLockedAccountResponse(Request $request)
    {
        return redirect()->back()->withInput($request->only($this->username(), 'remember'))->withErrors([
            $this->username() => $this->getLockedAccountMessage(),
        ]);
    }

    /**
     * Obtiene el mensaje a mostrar para la cuenta bloqueada.
     *
     * @method  getLockedAccountMessage
     *
     * @return string
     */
    protected function getLockedAccountMessage()
    {
        return Lang::has('auth.locked')
               ? Lang::get('auth.locked')
               : 'Tú cuenta esta bloqueada. Por favor contacte a soporte por ayuda.';
    }

    /**
     * Obtiene la instancia de la petición del usuario inactivo
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function sendInactiveAccountRequest(Request $request)
    {
        return redirect()->back()->withInput($request->only($this->username(), 'remember'))->withErrors([
            $this->username() => $this->getInactiveAccountMessage(),
        ]);
    }

    /**
     * Obtiene el mensaje a mostrar de la cuenta inactiva
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function getInactiveAccountMessage()
    {
        return 'Usted no esta autorizado para acceder a la aplicación. La cuenta está inactiva.';
    }

    /**
     * Obtiene la instancia de la petición del usuario con sesión activa
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function sendHasActiveSessionRequest(Request $request)
    {
        return redirect()->back()->withInput($request->only($this->username(), 'remember'))->withErrors([
            $this->username() => $this->getHasActiveSessionMessage(),
        ]);
    }

    /**
     * Obtiene el mensaje a mostrar de la sesión activa
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function getHasActiveSessionMessage()
    {
        return 'Usted ya tiene una sesión activa en la aplicación. Para iniciar sesión en este equipo debe cerrar la sesión activa.';
    }
}
