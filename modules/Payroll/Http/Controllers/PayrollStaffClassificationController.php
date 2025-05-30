<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollStaffClassification;

/**
 * @class StaffClassificationController
 * @brief Controlador de clasificación del personal
 *
 * Clase que gestiona las clasificaciones del personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffClassificationController extends Controller
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
        /* Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.staff.classifications.list', ['only' => 'index']);
        $this->middleware('permission:payroll.staff.classifications.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.staff.classifications.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.staff.classifications.delete', ['only' => 'destroy']);
        */
    }

    /**
     * Muestra todos los registros de la clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la clasificación del personal
     */
    public function index()
    {
        return response()->json(['records' => PayrollStaffClassification::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de clasificación del personal
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra una nuevo clasificación del personal
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
            'name' => ['required', 'max:100', 'unique:payroll_staff_classifications,name'],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollStaffClassification = PayrollStaffClassification::create([
            'name' => $request->name,'description' => $request->description
        ]);
        return response()->json(['record' => $payrollStaffClassification, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de la clasificación del personal
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar la clasificación del personal
     *
     * @param \Modules\Payroll\Models\PayrollStaffClassification $staff_classification Clasificación del personal
     *
     * @return \Illuminate\View\View
     */
    public function edit(PayrollStaffClassification $staff_classification)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información de la clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador de la clasificación del personal a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollStaffClassification = PayrollStaffClassification::find($id);
        $this->validate($request, [
            'name' => [
                'required', 'max:100', 'unique:payroll_staff_classifications,name,' . $payrollStaffClassification->id
            ],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollStaffClassification->name  = $request->name;
        $payrollStaffClassification->description = $request->description;
        $payrollStaffClassification->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador de la clasificación del personal a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollStaffClassification = PayrollStaffClassification::find($id);
        $payrollStaffClassification->delete();
        return response()->json(['record' => $payrollStaffClassification, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene la clasificación del personal registradas
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la clasificación del personal
     */
    public function getPayrollStaffClassifications()
    {
        return response()->json(
            template_choices('Modules\Payroll\Models\PayrollStaffClassification', 'name', '', true)
        );
    }
}
