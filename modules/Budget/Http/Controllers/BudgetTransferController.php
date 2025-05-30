<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\BudgetModification;

/**
 * @class BudgetTransferController
 * @brief Controlador de transferencias presupuestarias
 *
 * Clase que gestiona las transferencias presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetTransferController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:budget.transfer.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.transfer.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.transfer.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.transfer.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra un listado de transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* Objeto con información de las transferencias presupuestarias */
        $records = BudgetModification::where('type', 'T')->get();

        return view('budget::transfers.list');
    }

    /**
     * Muestra el formulario para la creación de transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => 'budget.transfers.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];
        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = template_choices(Institution::class, ['acronym', '-', 'name'], ['active' => true]);

        return view('budget::transfers.create-edit-form', compact('header', 'institutions'));
    }

    /**
     * Guarda información de las transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param int $id Identificador de la transferencia presupuesaria a mostrar
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('budget::show');
    }

    /**
     * Muestra el formulario de modificación de transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param int $id Identificador de la transferencia presupuestaria a modificar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        return view('budget::edit');
    }

    /**
     * Actualiza información de transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador de la transferencia presupuestaria a modificar
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina transferencias presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param integer $id Identificador de la transferencia presupuestaria a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto con información de la transferencia presupuestaria a eliminar */
        $BudgetTransfer = BudgetModification::find($id);

        if ($BudgetTransfer) {
            $BudgetTransfer->delete();
        }

        return response()->json(['record' => $BudgetTransfer, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json([
            'records' => BudgetModification::where('type', 'T')->get()
        ], 200);
    }
}
