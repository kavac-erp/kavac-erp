<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollLicenseDegree;

/**
 * @class PayrollLicenseDegreeController
 * @brief Controlador de grados de licencia de conducir
 *
 * Clase que gestiona los grados de licencia de conducir
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollLicenseDegreeController extends Controller
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
        /*$this->middleware('permission:payroll.license.degrees.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.license.degrees.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.license.degrees.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.license.degrees.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de grados de licencia de conducir
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos
     */
    public function index()
    {
        return response()->json(['records' => PayrollLicenseDegree::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo grado de licencia de conducir
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo grado de licencia de conducir
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
            'name' => ['required', 'max:50', 'unique:payroll_license_degrees,name'],
            'description' => ['nullable', 'max:200'],
        ]);

        $payrollLicenseDegree = PayrollLicenseDegree::create([
            'name' => $request->name, 'description' => $request->description
        ]);
        return response()->json(['record' => $payrollLicenseDegree, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de un grado de licencia de conducir
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para actualizar la información de un grado de licencia de conducir
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información de grados de licencia de conducir
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del grado de licencia de conducir a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollLicenseDegree = PayrollLicenseDegree::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:50', 'unique:payroll_license_degrees,name,' . $payrollLicenseDegree->id],
            'description' => ['nullable', 'max:200'],
        ]);

        $payrollLicenseDegree->name = $request->name;
        $payrollLicenseDegree->description = $request->description;
        $payrollLicenseDegree->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el grado de licencia de conducir
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del grado de licencia de conducir a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollLicenseDegree = PayrollLicenseDegree::find($id);
        $payrollLicenseDegree->delete();
        return response()->json(['record' => $payrollLicenseDegree, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los grados de licencia de conducir
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los grados de licencia de conducir
     */
    public function getPayrollLicenseDegrees()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollLicenseDegree', 'name', '', true));
    }
}
