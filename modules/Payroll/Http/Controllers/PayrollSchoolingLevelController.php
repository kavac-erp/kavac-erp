<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSchoolingLevel;

/**
 * @class PayrollSchoolingLevelController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSchoolingLevelController extends Controller
{
    use ValidatesRequests;

    protected $rules;

    /**
     * Define la configuración de la clase
     *
     * @author [José Briceño] [josejorgebriceno9@gmail.com]
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.schooling-levels.create', ['only' => ['index', 'create', 'store']]);

        $this->rules = [
            'name' => ['required', 'max:100', 'unique:payroll_schooling_levels,name'],
            'description' => ['nullable', 'max:200']
        ];
    }

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => PayrollSchoolingLevel::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [José Briceño] [josejorgebriceno9@gmail.com]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [description de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function destroy($id)
    {
        $payrollSchoolingLevel = PayrollSchoolingLevel::find($id);
        $payrollSchoolingLevel->delete();
        return response()->json(['record' => $payrollSchoolingLevel, 'message' => 'Success'], 200);
    }

    public function getPayrollSchoolingLevels()
    {
        return response()->json(template_choices(PayrollSchoolingLevel::class, 'name', '', true));
    }
}
