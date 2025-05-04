<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Roles\Exceptions\RoleDeniedException;
use App\Roles\Exceptions\LevelDeniedException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use App\Roles\Exceptions\PermissionDeniedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use PhpOffice\PhpSpreadsheet\Reader\Exception as PhpOfficeException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @class Handler
 * @brief Gestiona las excepciones y/o errores generados por la aplicación
 *
 * Gestiona las excepciones de la aplicación
 */
class Handler extends ExceptionHandler
{
    /**
     * Listado de tipos de excepciones que no son reportadas.
     *
     * @var array $dontReport
     */
    protected $dontReport = [
        //
    ];

    /**
     * Listado de campos que no son capturados para la validación de excepciones
     *
     * @var array $dontFlash
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Reporta o registra una excepción
     *
     * @param  \Throwable  $exception
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Genera una excepción dentro de una respuesta HTTP.
     *
     * @param  Request  $request Datos de la petición
     * @param  \Throwable  $exception Datos de la exccepción generada
     *
     * @return JsonResponse|RedirectResponse|Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception->getMessage() === 'Invalid signature.' &&  $request->routeIs('verification.verify')) {
            // Captura una excepción por enlace de verificación expirado o enlace con firma inválida
            abort(410);
        }
        if ($exception instanceof TokenMismatchException) {
            // Captura una excepción por inactividad
            session()->flash('message', ['type' => 'deny', 'msg' => 'Sesión expirada por inactividad.']);
            return redirect()->route('index');
        }

        if ($exception instanceof MethodNotAllowedHttpException && $request->path() === "logout") {
            // Captura una excepción cuando el método usado no esta permitido
            session()->flash('message', ['type' => 'deny', 'msg' => 'Usted ha salido del sistema']);
            return redirect()->route('index');
        }

        if (
            $exception instanceof PermissionDeniedException ||
            $exception instanceof LevelDeniedException ||
            $exception instanceof RoleDeniedException
        ) {
            if ($exception instanceof PermissionDeniedException) {
                // Mensaje de restricción de acceso para cuando no dispone de los permisos requeridos
                $msg = 'No dispone de permisos para acceder a esta funcionalidad';
            } elseif ($exception instanceof LevelDeniedException) {
                // Mensaje de restricción de acceso para cuando no dispone del nivel de acceso requerido
                $msg = 'Su nivel de acceso no le permite acceder a esta funcionalidad';
            } elseif ($exception instanceof RoleDeniedException) {
                // Mensaje de restricción de acceso para cuando no dispone del rol requerido
                $msg = 'El rol asignado no le permite acceder a esta funcionalidad';
            }

            if ($request->ajax()) {
                return response()->json(['result' => false, 'message' => $msg], 403);
            }

            $request->session()->flash('message', ['type' => 'deny', 'msg' => $msg]);

            if (url()->current() === url()->previous()) {
                return redirect()->route('index');
            }

            return redirect()->back();
        }

        if ($exception instanceof PhpOfficeException) {
            // Excepción capturada cuando un archivo a importar es inválido
            $msg = 'El archivo a importar es inválido. Revise que los datos de la cabecera sean ' .
                   'correctos y que contenga información.';

            if ($request->ajax()) {
                return response()->json(['result' => false, 'message' => $msg], 200);
            }

            $request->session()->flash('message', [
                'type' => 'other', 'msg' => $msg, 'title' => 'Error!', 'icon' => 'screen-error',
                'class' => 'growl-danger'
            ]);
        }

        if ($exception instanceof \Swift_TransportException || $exception->getCode() === 530) {
            // Excepción capturada cuando se genera un error con la plataforma de transporte para el envío de correos
            $msg = 'Error del sistema. Si el problema persiste contacte al administrador';

            if ($request->ajax()) {
                return response()->json(['result' => false, 'message' => $msg], 200);
            }

            $request->session()->flash('message', [
                'type' => 'other', 'msg' => $msg, 'text' => $msg, 'title' => 'Error!', 'icon' => 'screen-error',
                'class' => 'growl-danger'
            ]);

            return redirect()->back();
        }

        if ($exception instanceof ClosedFiscalYearException) {
            // Excepción capturada cuando se intenta acceder a un recurso que no esta disponible por cierre fiscal
            $msg = $exception->getMessage();

            if ($request->ajax()) {
                return response()->json(['result' => false, 'message' => $msg], 403);
            }

            $request->session()->flash('message', [
                'type' => 'other', 'msg' => $msg, 'text' => $msg, 'title' => 'Error!', 'icon' => 'screen-error',
                'class' => 'growl-danger'
            ]);

            return redirect()->back();
        }

        if ($exception instanceof RestrictedRegistryDeletionException) {
            /*
             * Excepción capturada cuando se intenta eliminar registros que
             * tengan relación con otros.
             */
            $msg = $exception->getMessage();

            if ($request->ajax()) {
                return response()->json(['result' => false, 'message' => $msg], 403);
            }

            $request->session()->flash('message', [
                'type' => 'other',
                'msg' => $msg,
                'text' => $msg,
                'title' => 'Error!',
                'icon' => 'screen-error',
                'class' => 'growl-danger'
            ]);

            return redirect()->back();
        }

        return parent::render($request, $exception);
    }
}
