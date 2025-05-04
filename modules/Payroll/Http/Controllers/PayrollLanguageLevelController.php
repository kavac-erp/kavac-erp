<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollLanguageLevel;

/**
 * @class PayrollLanguageLevelController
 * @brief Controlador del nivel de idioma
 *
 * Clase que gestiona los niveles de idioma
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollLanguageLevelController extends Controller
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
        /*$this->middleware('permission:payroll.language.levels.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.language.levels.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.language.levels.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.language.levels.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros del nivel de idioma
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos del nivel de idioma
     */
    public function index()
    {
        return response()->json(['records' => PayrollLanguageLevel::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo nivel de idioma
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo nivel de idioma
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
            'name' => ['required', 'max:100', 'unique:payroll_language_levels,name']
        ]);
        $payrollLanguageLevel = PayrollLanguageLevel::create(['name' => $request->name]);
        return response()->json(['record' => $payrollLanguageLevel, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información del nivel de idioma
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para actualizar la información del nivel de idioma
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del nivel de idioma
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del nivel de idioma  a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollLanguageLevel = PayrollLanguageLevel::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_language_levels,name,' . $payrollLanguageLevel->id]
        ]);
        $payrollLanguageLevel->name  = $request->name;
        $payrollLanguageLevel->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el nivel de idioma
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del nivel de idioma a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollLanguageLevel = PayrollLanguageLevel::find($id);
        $payrollLanguageLevel->delete();
        return response()->json(['record' => $payrollLanguageLevel, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los niveles de idioma registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos del nivel de idioma
     */
    public function getPayrollLanguageLevels()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollLanguageLevel', 'name', '', true));
    }
}
