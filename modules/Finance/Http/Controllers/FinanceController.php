<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\Models\CodeSetting;
use App\Rules\CodeSetting as CodeSettingRule;
use Modules\Finance\Models\FinanceCheckBook;
use Modules\Finance\Models\FinanceBankingMovement;
use Modules\Finance\Models\FinancePaymentDeduction;
use Modules\Finance\Models\FinancePaymentExecute;
use Modules\Finance\Models\FinancePayOrder;
use Modules\Finance\Models\FinanceConciliation;

/**
 * @class FinanceController
 * @brief Controlador principal del módulo de finanzas
 *
 * Clase que gestiona el módulo de finanzas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceController extends Controller
{
    /**
     * Muestra registros del módulo de finanzas
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('finance::index');
    }

    /**
     * Muestra el formulario de creación de un nuevo registro
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Almacena un nuevo registro de configuración de códigos en el módulo de finanzas
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'checks_code' => [new CodeSettingRule()],
            'movements_code' => [new CodeSettingRule()],
            'pay_orders_code' => [new CodeSettingRule()],
            'payment_executes_code' => [new CodeSettingRule()],
            'conciliations_code' => [new CodeSettingRule()],
        ]);

        /* Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /* Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /* Define el modelo al cual hace referencia el código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                if ($table === "check_books") {
                    $table = "check_books";
                    $model = FinanceCheckBook::class;
                } elseif ($table === "movements") {
                    $table = "movements_code";
                    $model = FinanceBankingMovement::class;
                } elseif ($table === "payOrders") {
                    $table = "pay_orders";
                    $model = FinancePayOrder::class;
                } elseif ($table === "paymentExecutes") {
                    $table = "payment_executes";
                    $model = FinancePaymentExecute::class;
                } elseif ($table === "conciliations") {
                    $table = "conciliations";
                    $model = FinanceConciliation::class;
                }

                $codeSetting = CodeSetting::where([
                    'module' => 'finance',
                    'table'  => 'finance_' . $table,
                    'field'  => $field
                ])->first();

                if (!isset($codeSetting)) {
                    $codeSetting = CodeSetting::create([
                        'module'        => 'finance',
                        'table'         => 'finance_' . $table,
                        'field'         => $field,
                        'format_prefix' => $prefix,
                        'format_digits' => $digits,
                        'format_year'   => $sufix,
                        'model'         => $model
                    ]);
                }

                /* Define el estado verdadero para indicar que se ha registrado información */
                $saved = true;
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->back();
    }

    /**
     * Muestra detalles de un registro
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario de edición de un registro
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Actualiza un registro
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina un registro
     *
     * @return void
     */
    public function destroy()
    {
        //
    }

    /**
     * Gestiona la configuración para los cheques a emitir
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function setting()
    {
        $checkCode = CodeSetting::where('model', FinanceCheckBook::class)->first() ?? '';
        $movementCode = CodeSetting::where('model', FinanceBankingMovement::class)->first() ?? '';
        $payOrderCode = CodeSetting::where('model', FinancePayOrder::class)->first() ?? '';
        $paymentExecutesCode = CodeSetting::where('model', FinancePaymentExecute::class)->first() ?? '';
        $conciliationCode = CodeSetting::where('model', FinanceConciliation::class)->first() ?? '';
        return view(
            'finance::settings',
            compact(
                'checkCode',
                'movementCode',
                'payOrderCode',
                'paymentExecutesCode',
                'conciliationCode'
            )
        );
    }

    /**
     * Obtiene información de las deducciones a pagar
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getDeductionsToPay(Request $request)
    {
        $deductions_ids = json_decode($request->deductions_ids);
        $deductions = [];
        if ($deductions_ids) {
            $deductions = FinancePaymentDeduction::query()
            ->with('deduction')
            ->whereIn('id', $deductions_ids)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($deduc) {
                return [
                    'id' => $deduc->id,
                    'name' => $deduc->deduction->name,
                    'amount' => $deduc->amount,
                    'deducted_at' => date('d/m/Y', strtotime($deduc->deducted_at)),
                ];
            });
        }
        return response()->json(['records' => $deductions], 200);
    }
}
