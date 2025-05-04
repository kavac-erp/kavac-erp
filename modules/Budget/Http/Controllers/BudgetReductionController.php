<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\BudgetModification;

/**
 * @class BudgetReductionController
 * @brief Controlador de reducciones presupuestarias
 *
 * Clase que gestiona las reducciones presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetReductionController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con los datos a implementar en los atributos del formulario
     *
     * @var array $header
     */
    public $header;

    /**
     * Arreglo con información de las instituciones registradas
     *
     * @var array $institution
     */
    public $institution;

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
        $this->middleware('permission:budget.reduction.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.reduction.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.reduction.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.reduction.delete', ['only' => 'destroy']);

        /* Arreglo de opciones a implementar en el formulario */
        $this->header = [
            'route' => 'budget.reductions.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $this->institution = template_choices(Institution::class, ['acronym', '-', 'name'], ['active' => true]);
    }

    /**
     * Muestra un listado de reducciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* Objeto con información de las reducciones presupuestarias */
        $records = BudgetModification::where('type', 'R')->get();

        return view('budget::reductions.list');
    }

    /**
     * Muestra un formulario para la creación de redcciones presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* Arreglo de opciones a implementar en el formulario */
        $header = $this->header;
        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = $this->institution;

        return view('budget::reductions.create-edit-form', compact('header', 'institutions'));
    }

    /**
     * Guarda información de las reducciones presupuestarias
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
     * Muestra información de las reducciones presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param int $id Identificador de la reducción presupuestaria a mostrar
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('budget::show');
    }

    /**
     * Muestra el formulario para la edición de formulaciones presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param int $id Identificador de la reducción presupuestaria a modificar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        return view('budget::edit');
    }

    /**
     * Actualiza información de las reducciones presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Datos de la petición
     * @param int $id          Identificador de la reducción presupuestaria a modificar
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina una reducción presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Datos de la petición
     * @param int $id Identificador de la reducción presupuestaria a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        /* Objeto con información de la reducción presupuestaria a eliminar */
        $BudgetReduction = BudgetModification::find($id);

        if ($BudgetReduction) {
            $BudgetReduction->delete();
        }

        return response()->json(['record' => $BudgetReduction, 'message' => 'Success'], 200);
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
            'records' => BudgetModification::where('type', 'R')->get()
        ], 200);
    }
}
