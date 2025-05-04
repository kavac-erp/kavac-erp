<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Http\Controllers;

use App\Models\Institution;
use Illuminate\Routing\Controller;
use Modules\Payroll\Jobs\SendReceiptJob;
use Illuminate\Contracts\Support\Renderable;

/**
 * @class SendPayrollPaymentTypeReceiptsController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SendPayrollPaymentTypeReceiptsController extends Controller
{
    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('payroll::index');
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('payroll::create');
    }

    public function __construct()
    {
        $this->middleware('permission:payroll.payment-receipts.send', ['only' => ['sendReceipts']]);
    }

    /**
     * [descripción del método]
     *
     * @method    sendReceipts
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
