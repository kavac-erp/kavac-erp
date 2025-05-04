<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollLanguage;

/**
 * @class PayrollLanguageController
 * @brief Controlador de idioma
 *
 * Clase que gestiona los idiomas
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollLanguageController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación para el formulario
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Atributos para los campos personalizados
     *
     * @var array $attributes
     */
    protected $attributes;

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
        /*$this->middleware('permission:payroll.languages.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.languages.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.languages.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.languages.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->rules = [
            'name' => [],
            'acronym' => ['required', 'max:10'],
        ];

        /* Define los atributos para los campos personalizados */
        $this->attributes = [
            'acronym' => 'acrónimo',
        ];
    }

    /**
     * Muestra todos los registros de idiomas
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de idiomas
     */
    public function index()
    {
        return response()->json(['records' => PayrollLanguage::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo idioma
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo idioma
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->rules['name'] = ['required', 'unique:payroll_languages,name'];
        $this->validate($request, $this->rules, [], $this->attributes);
        $payrollLanguage = PayrollLanguage::create(['name' => $request->name, 'acronym' => $request->acronym]);
        return response()->json(['record' => $payrollLanguage, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información del idioma
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar el idioma
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del idioma
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del idioma a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollLanguage = PayrollLanguage::find($id);
        $this->rules['name'] = ['required', 'unique:payroll_languages,name,' . $payrollLanguage->id];
        $this->validate($request, $this->rules, [], $this->attributes);
        $payrollLanguage->name  = $request->name;
        $payrollLanguage->acronym  = $request->acronym;
        $payrollLanguage->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el idioma
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del idioma a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollLanguage = PayrollLanguage::find($id);
        $payrollLanguage->delete();
        return response()->json(['record' => $payrollLanguage, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los idiomas registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de idiomas
     */
    public function getPayrollLanguages()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollLanguage', 'name', '', true));
    }
}
