<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollInstructionDegree;

/**
 * @class PayrollInstructionDegreeController
 * @brief Controlador de grado de instrucción
 *
 * Clase que gestiona los grados de instrucción
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollInstructionDegreeController extends Controller
{
    use ValidatesRequests;

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
        /*$this->middleware('permission:payroll.instruction.degrees.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.instruction.degrees.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.instruction.degrees.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.instruction.degrees.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de grado de instruccíón
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de grado de instruccíón
     */
    public function index()
    {
        return response()->json(['records' => PayrollInstructionDegree::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo grado de instrucción
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo grado de instrucción
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_instruction_degrees,name'],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollInstructorDegree = PayrollInstructionDegree::create([
            'name' => $request->name, 'description' => $request->description
        ]);
        return response()->json(['record' => $payrollInstructorDegree, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un grado de instrucción
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para actualizar la información de un grado de instrucción
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del grado de instrucción
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del grado de instrucción a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollInstructorDegree = PayrollInstructionDegree::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_instruction_degrees,name,' . $payrollInstructorDegree->id],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollInstructorDegree->name  = $request->name;
        $payrollInstructorDegree->description = $request->description;
        $payrollInstructorDegree->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el grado de instrucción
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del grado de instrucción a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollInstructorDegree = PayrollInstructionDegree::find($id);
        $payrollInstructorDegree->delete();
        return response()->json(['record' => $payrollInstructorDegree, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los grados de instrucción registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de grados de instrucción
     */
    public function getPayrollInstructionDegrees()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollInstructionDegree', 'name', '', true));
    }
}
