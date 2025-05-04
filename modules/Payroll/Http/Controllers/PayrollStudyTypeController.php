<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollStudyType;

/**
 * @class PayrollStudyTypeController
 * @brief Controlador de tipo de estudio
 *
 * Clase que gestiona los tipos de estudios
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class PayrollStudyTypeController extends Controller
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
        /*$this->middleware('permission:payroll.study.types.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.study.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.study.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.study.types.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de estudio
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de estudio
     */
    public function index()
    {
        return response()->json(['records' => PayrollStudyType::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de tipo de estudio
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de estudio
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
            'name' => ['required', 'max:100', 'unique:payroll_study_types,name'],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollStudyType = PayrollStudyType::create(['name' => $request->name,'description' => $request->description]);
        return response()->json(['record' => $payrollStudyType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de estudio
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar la información de un tipo de estudio
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del tipo de estudio
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del tipo de estudio a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollStudyType = PayrollStudyType::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_study_types,name,' . $payrollStudyType->id],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollStudyType->name  = $request->name;
        $payrollStudyType->description = $request->description;
        $payrollStudyType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de estudio
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del tipo de estudio a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollStudyType = PayrollStudyType::find($id);
        $payrollStudyType->delete();
        return response()->json(['record' => $payrollStudyType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de estudio registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de estudio
     */
    public function getPayrollStudyTypes()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollStudyType', 'name', '', true));
    }
}
