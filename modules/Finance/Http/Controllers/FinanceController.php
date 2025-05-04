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

/**
 * @class FinanceController
 * @brief Controlador principal del módulo de finanzas
 *
 * Clase que gestiona el módulo de finanzas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('finance::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('finance::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'checks_code' => [new CodeSettingRule()],
            'movements_code' => [new CodeSettingRule()],
            'pay_orders_code' => [new CodeSettingRule()],
            'payment_executes_code' => [new CodeSettingRule()],
        ]);

        /** @var array $codes Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /** @var boolean $saved Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /** @var string $model Define el modelo al cual hace referencia el código */
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

                /** @var boolean $saved Define el estado verdadero para indicar que se ha registrado información */
                $saved = true;
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->back();
    }

    /**
     * Show the specified resource.
     * @return Renderable
     */
    public function show()
    {
        return view('finance::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     */
    public function edit()
    {
        return view('finance::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Renderable
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Renderable
     */
    public function destroy()
    {
    }

    /**
     * Gestiona la configuración para los cheques a emitir
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return Renderable
     */
    public function setting()
    {
        $checkCode = CodeSetting::where('model', FinanceCheckBook::class)->first() ?? '';
        $movementCode = CodeSetting::where('model', FinanceBankingMovement::class)->first() ?? '';
        $payOrderCode = CodeSetting::where('model', FinancePayOrder::class)->first() ?? '';
        $paymentExecutesCode = CodeSetting::where('model', FinancePaymentExecute::class)->first() ?? '';
        return view('finance::settings', compact('checkCode', 'movementCode', 'payOrderCode', 'paymentExecutesCode'));
    }

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
