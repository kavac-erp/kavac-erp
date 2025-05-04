<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\PayrollRelationship;

/**
 * @class PayrollRelationshipController
 * @brief Controlador de parentescos
 *
 * Clase que gestiona los datos de parentescos
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollRelationshipController extends Controller
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
        /* Establece permisos de acceso para cada método del controlador*/
        $this->middleware('permission:payroll.relationships.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.relationships.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.relationships.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.relationships.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de parentescos
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos
     */
    public function index()
    {
        return response()->json(['records' => PayrollRelationship::all()], 200);
    }

    /**
     * Muestra el formulario para registrar una nueva relación
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo tipo de liquidación
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:payroll_relationships,name'],
            'description' => ['nullable', 'max:200']
        ]);

        $payrollRelationship = PayrollRelationship::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
        return response()->json(['record' => $payrollRelationship, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de uan relación
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
     * Muestra el formulario para editar una relación
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
     * Actualiza el parentesco
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     * @author    Manuel Zambrano <mazambrano@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $payrollRelationship = PayrollRelationship::find($id);
            $seedRegisters = get_json_resource('Data/PayrollRelationship.json', 'payroll');
            $payrollRelationshipRegister = $payrollRelationship
                ->makeHidden(['id','created_at','updated_at', 'deleted_at'])
                ->toArray();
            if (!in_array((object)$payrollRelationshipRegister, $seedRegisters)) {
                $this->validate($request, [
                    'name' => ['required', 'max:100', 'unique:payroll_relationships,name,' . $payrollRelationship->id],
                    'description' => ['nullable', 'max:200']
                ]);
                $payrollRelationship->name = $request->name;
                $payrollRelationship->description = $request->description;
                $payrollRelationship->save();
                return response()->json(['message' => 'Success'], 200);
            } else {
                return response()->json(['error' => true, 'message'
                    => 'No se puede Modificar el registro'], 403);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['error' => true, 'message' => __($th->getMessage())], 200);
        }
    }

    /**
     * Obtiene los tipos de relacionesregistrados
     *
     * @author  Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los tipos de relaciones
     */
    public function getPayrollRelationship()
    {
        return response()->json(template_choices('Modules\Payroll\Models\PayrollRelationship', 'name', '', true));
    }

    /**
     * Elimina el parentesco
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        try {
            $seedRegisters = get_json_resource('Data/PayrollRelationship.json', 'payroll');
            $payrollRelationship = PayrollRelationship::find($id);
            $payrollRelationshipRegister = $payrollRelationship
                ->makeHidden(['id','created_at','updated_at', 'deleted_at'])
                ->toArray();
            if (!in_array((object)$payrollRelationshipRegister, $seedRegisters)) {
                $payrollRelationship->delete();
                return response()->json(['record' => $payrollRelationshipRegister, 'message' => 'Success'], 200);
            } else {
                return response()->json(['error' => true, 'message'
                    => 'No se puede Eliminar el registro'], 403);
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => true, 'message' => __($e->getMessage())], 200);
        }
    }
}
