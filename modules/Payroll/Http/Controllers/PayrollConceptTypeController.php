<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollConceptType;
use Modules\Payroll\Models\PayrollConcept;

/**
 * @class      PayrollConceptTypeController
 * @brief      Controlador de tipo de concepto
 *
 * Clase que gestiona los tipos de concepto
 *
 * @author     William Páez <wpaez at cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConceptTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        /*$this->middleware('permission:payroll.concept.types.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.concept.types.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.concept.types.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.concept.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'        => ['required', 'max:100', 'unique:payroll_concept_types,name'],
            'description' => ['nullable', 'max:200'],
            'sign'        => ['required', 'max:2']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'sign.required' => 'El campo signo es obligatorio.',
            'sign.max:2'    => 'El campo signo no cumple el formato adecuado.',
        ];
    }

    /**
     * Listado de tipos de conceptos de nómina
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PayrollConceptType::all()], 200);
    }

    /**
     * Almacena un nuevo tipo de concepto
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        /* Objeto asociado al modelo PayrollConceptType */
        $payrollConceptType = PayrollConceptType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'sign'        => $request->sign
        ]);
        return response()->json(['record' => $payrollConceptType, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza los datos de un tipo de concepto
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id     ID del tipo de concepto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Objeto con la información del tipo de pago a editar asociado al modelo PayrollConceptType */
        $payrollConceptType = PayrollConceptType::find($id);
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'max:100', 'unique:payroll_concept_types,name,' . $payrollConceptType->id]]
        );
        $this->validate($request, $validateRules, $this->messages);

        $payrollConceptType->name  = $request->name;
        $payrollConceptType->description = $request->description;
        $payrollConceptType->sign = $request->sign;
        $payrollConceptType->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un tipo de concepto
     *
     * @param  integer $id ID del tipo de concepto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto con la información del tipo de pago a eliminar asociado al modelo PayrollConceptType */
        $payrollConceptType = PayrollConceptType::find($id);
        if ($payrollConceptType) {
            $payrollConcept = PayrollConcept::where('payroll_concept_type_id', $id)->first();
            if ($payrollConcept) {
                return response()->json(
                    [
                        'error' => true,
                        'message' => 'El registro no puede ser eliminado ' .
                        ' porque está relacionado con registros de conceptos.'
                    ],
                    200
                );
            }
            $payrollConceptType->delete();
        }
        return response()->json(['record' => $payrollConceptType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de conceptos registrados
     *
     * @return array Listado de los registros a mostrar
     */
    public function getPayrollConceptTypes()
    {
        $payrollConceptTypes = PayrollConceptType::query()->select('id', 'name as text', 'sign')->get()->toarray();
        return array_merge(array(['id' => '', 'text' => 'Seleccione...']), $payrollConceptTypes);
    }
}
