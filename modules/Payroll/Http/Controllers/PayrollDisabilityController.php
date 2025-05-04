<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollDisability;

/**
 * @class PayrollDisabilityController
 * @brief Controlador de discapacidades
 *
 * Clase que gestiona los datos de discapacidades
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollDisabilityController extends Controller
{
    use ValidatesRequests;

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
        /*$this->middleware('permission:payroll.disabilities.list', ['only' => ['index', 'vueList']]);*/
        $this->middleware('permission:payroll.disabilities.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.disabilities.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.disabilities.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de discapacidades
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos
     */
    public function index()
    {
        return response()->json(['records' => PayrollDisability::all()], 200);
    }

    /**
     * Muestra el formulario para registrar una nueva discapacidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra una nueva discapacidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_disabilities,name'],
            'description' => ['nullable', 'max:200']
        ]);

        $payrollDisability = PayrollDisability::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return response()->json(['record' => $payrollDisability, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los datos de una discapacidad
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
     * Muestra el formulario para editar una discapacidad
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
     * Actualiza la discapacidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollDisability = PayrollDisability::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_disabilities,name,' . $payrollDisability->id],
            'description' => ['nullable', 'max:200']
        ]);

        $payrollDisability->name = $request->name;
        $payrollDisability->description = $request->description;
        $payrollDisability->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la discapacidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollDisability = PayrollDisability::find($id);
        $payrollDisability->delete();
        return response()->json(['record' => $payrollDisability, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las discapacidades registradas
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos
     */
    public function getPayrollDisabilities()
    {
        return response()->json(template_choices(PayrollDisability::class, 'name', '', true));
    }
}
