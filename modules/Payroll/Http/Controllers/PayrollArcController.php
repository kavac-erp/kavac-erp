<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Payroll\Actions\GetPayrollArcAction;
use Modules\Payroll\Jobs\PayrollSendArcJob;

/**
 * @class PayrollArcController
 * @brief Controlador para gestionar la información de la planilla ARC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollArcController extends Controller
{
    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.arc.list', ['only' => ['index', 'getArcRegisters']]);
        $this->middleware('permission:payroll.arc.send', ['only' => 'send']);
        $this->middleware('permission:payroll.arc.export', ['only' => 'export']);
    }

    /**
     * Muestra la lista de registros de la planilla ARC
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::arc.index');
    }

    /**
     * Obtiene la lista de registros de la planilla ARC
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param \Modules\Payroll\Actions\GetPayrollArcAction $getPayrollArcAction Acción para obtener los registros
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArcRegisters(
        Request $request,
        GetPayrollArcAction $getPayrollArcAction
    ): JsonResponse {
        if (empty($request->fiscal_year)) {
            return response()->json(['data' => [], 'count' => 0], 200);
        }

        return $getPayrollArcAction->all($request);
    }

    /**
     * Descarga los registros de la planilla ARC
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param \Modules\Payroll\Actions\GetPayrollArcAction $getPayrollArcAction Acción para obtener los registros
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(
        Request $request,
        GetPayrollArcAction $getPayrollArcAction
    ) {
        if ($request->with_zip ?? false) {
            PayrollSendArcJob::dispatch(
                $request->input(),
                true,
                Auth::user()->id
            );

            return response()->json(['result' => true], 200);
        }

        return $getPayrollArcAction->export($request);
    }

    /**
     * Envia por correo los registros de la planilla ARC
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        PayrollSendArcJob::dispatch($request->input());

        return response()->json(['result' => true], 200);
    }
}
