<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Institution;
use Illuminate\Routing\Controller;
use Modules\Payroll\Jobs\SendReceiptJob;
use Illuminate\Contracts\Support\Renderable;

/**
 * @class SendPayrollPaymentTypeReceiptsController
 * @brief Controlador para gestionar los envíos de recibos de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SendPayrollPaymentTypeReceiptsController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:payroll.payment.receipts.send', ['only' => ['sendReceipts']]);
    }

    /**
     * Muestra la lista de registros de recibos de pago
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::');
    }

    /**
     * Muestra el formulario para crear un nuevo registro de recibos de pago
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Envía los recibos de pago
     *
     * @param     integer    $payroll_id    Identificador de la nómina
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function sendReceipts($payroll_id)
    {
        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::query()
                ->where(['id' => auth()->user()->profile->institution_id])
                ->first();
        } else {
            $institution = Institution::query()
                ->where(['active' => true, 'default' => true])
                ->first();
        }

        SendReceiptJob::dispatch(
            $payroll_id,
            $institution->id
        );

        return response()->json(['result' => true], 200);
    }
}
