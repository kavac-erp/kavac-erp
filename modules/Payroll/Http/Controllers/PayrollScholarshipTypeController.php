<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollScholarshipType;

/**
 * @class PayrollScholarshipTypeController
 * @brief Controlador de tipos de becas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollScholarshipTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.scholarship.types.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.scholarship.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.scholarship.types.delete', ['only' => 'destroy']);
    }

    /**
     * Obtiene todos los registros de tipos de becas
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PayrollScholarshipType::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de beca
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Almacena un nuevo tipo de beca
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'max:100', 'unique:payroll_scholarship_types,name'],
                'description' => ['nullable', 'max:200']
            ],
            [
                'name.unique' => 'Ya ha sido registrado un tipo de beca con ese nombre.'
            ],
        );

        $payrollScholarshipType = PayrollScholarshipType::create([
            'name' => $request->name, 'description' => $request->description
        ]);

        return response()->json(['record' => $payrollScholarshipType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información sobre un tipo de beca
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
     * Muestra el formulario para editar un tipo de beca
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
     * Actualiza la información de un tipo de beca
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, PayrollScholarshipType $payrollScholarshipType, $id)
    {
        $payrollScholarshipType = PayrollScholarshipType::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_scholarship_types,name,' . $payrollScholarshipType->id],
            'description' => ['nullable', 'max:200']
        ]);
        $payrollScholarshipType->name  = $request->name;
        $payrollScholarshipType->description = $request->description;
        $payrollScholarshipType->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de becas registrados
     *
     * @author  Francisco Escala
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de becas
     */
    public function getPayrollScholarshipType()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollScholarshipType', 'name', '', true));
    }

    /**
     * Elimina un tipo de beca
     *
     * @author    Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(PayrollScholarshipType $payrollScholarshipType, $id)
    {
        try {
            $payrollScholarshipType = PayrollScholarshipType::find($id);
            $payrollScholarshipType->delete();
            return response()->json(['record' => $payrollScholarshipType, 'message' => 'Success'], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => true, 'message' => __($e->getMessage())], 403);
        }
    }
}
