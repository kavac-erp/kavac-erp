<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSettlementType;

/**
 * @class PayrollSettlementTypeController
 * @brief Controlador de tipos de liquidación
 *
 * Clase que gestiona los datos de tipos de liquidación
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSettlementTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Arreglo con los atributos para las reglas de validación
     *
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        /*$this->middleware('permission:payroll.settlement.types.list', ['only' => ['index', 'vueList']]);*/
        $this->middleware('permission:payroll.settlement.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.settlement.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.settlement.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->rules = [
            'name' => [],
            'motive' => ['required', 'max:10'],
            // 'payroll_concept_id' => ['required'],
            'payroll_payment_types_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'motive.max' => 'El campo motivo no debe ser mayor que 10 caracteres',
        ];

        /* Define los atributos para los campos personalizados */
        $this->attributes = [
            'motive' => 'motivo',
            // 'payroll_concept_id' => 'concepto'
            'payroll_payment_types_id' => 'tipo de pago',
        ];
    }

    /**
     * Muestra todos los registros de tipos de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PayrollSettlementType::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de liquidación
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->rules['name'] = ['required', 'unique:payroll_settlement_types,name'];
        $this->validate($request, $this->rules, $this->messages, $this->attributes);
        $payrollSettlementType = PayrollSettlementType::create([
            'name' => $request->name,
            'motive' => $request->motive,
            'payroll_payment_types_id' => $request->payroll_payment_types_id
        ]);
        return response()->json(['record' => $payrollSettlementType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información del tipo de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar un tipo de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza el tipo de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollSettlementType = PayrollSettlementType::find($id);
        $this->rules['name'] = ['required', 'unique:payroll_settlement_types,name,' . $payrollSettlementType->id];
        $this->validate($request, $this->rules, $this->messages, $this->attributes);
        $payrollSettlementType->name = $request->name;
        $payrollSettlementType->motive = $request->motive;
        $payrollSettlementType->payroll_payment_types_id = $request->payroll_payment_types_id;
        $payrollSettlementType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollSettlementType = PayrollSettlementType::find($id);
        $payrollSettlementType->delete();
        return response()->json(['record' => $payrollSettlementType, 'message' => 'Success'], 200);
    }
}
