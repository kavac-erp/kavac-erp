<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSchoolingLevel;

/**
 * @class PayrollSchoolingLevelController
 * @brief Controlador de niveles de escolaridad
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSchoolingLevelController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Define la configuración de la clase
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.schooling.levels.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:payroll.schooling.levels.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.schooling.levels.delete', ['only' => ['destroy']]);

        $this->rules = [
            'name' => ['required', 'max:100', 'unique:payroll_schooling_levels,name'],
            'description' => ['nullable', 'max:200']
        ];
    }

    /**
     * Obtiene todos los registros de niveles de escolaridad
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PayrollSchoolingLevel::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo nivel de escolaridad
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Almacena un nuevo nivel de escolaridad
     *
     * @author    José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules, [], []);
        $payrollSchoolingLevel = PayrollSchoolingLevel::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return response()->json(['record' => $payrollSchoolingLevel, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un nivel de escolaridad
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
     * Muestra el formulario para editar un nivel de escolaridad
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
     * Actualiza la información de un nivel de escolaridad
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollSchoolingLevel = PayrollSchoolingLevel::find($id);
        $this->rules = [
            'name' => ['required', 'max:100', 'unique:payroll_schooling_levels,name,' . $payrollSchoolingLevel->id],
            'description' => ['nullable', 'max:200']
        ];
        $this->validate($request, $this->rules, [], []);
        $payrollSchoolingLevel->name = $request->name;
        $payrollSchoolingLevel->description = $request->description;
        $payrollSchoolingLevel->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un registro de niveles de escolaridad
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollSchoolingLevel = PayrollSchoolingLevel::find($id);
        $payrollSchoolingLevel->delete();
        return response()->json(['record' => $payrollSchoolingLevel, 'message' => 'Success'], 200);
    }

    /**
     * Listado de niveles de escolaridad
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getPayrollSchoolingLevels()
    {
        return response()->json(template_choices(PayrollSchoolingLevel::class, 'name', '', true));
    }
}
