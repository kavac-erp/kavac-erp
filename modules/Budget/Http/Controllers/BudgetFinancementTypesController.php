<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\BudgetFinancementTypes;

/**
 * @class BudgetFinancementTypesController
 *
 * @brief Gestión de las fuentes de financiamiento.
 *
 * Clase que gestiona las fuentes de financiamiento.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetFinancementTypesController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de los tipos de financiamiento
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Define la configuración inicial de la clase.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Primer registro para los selects. */
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];

        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:budget.financementtypes.index', ['only' => 'index']);
        $this->middleware('permission:budget.financementtypes.store', ['only' => 'store']);
        $this->middleware('permission:budget.financementtypes.update', ['only' => 'update']);
        $this->middleware('permission:budget.financementtypes.destroy', ['only' => 'destroy']);
    }

    /**
     * Obtiene un listado de los registros almacenados.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => BudgetFinancementTypes::orderBy('id')->get()], 200);
    }

    /**
     * Almacena un registro recién creado en la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required','unique:budget_financement_types']
            ],
            [
                'name.required' => 'El nombre de la fuente de financiamiento es obligatorio.',
                'name.unique' => 'El nombre de la fuente de financiamiento ya ha sido registrado.',
            ]
        );

        $data = DB::transaction(function () use ($request) {
            $data = BudgetFinancementTypes::create([
                'name' => $request->name
            ]);
            return $data;
        });
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles de un tipo de financiamiento
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Actualiza un registro específico de la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     * @param  integer                  $id     ID del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = BudgetFinancementTypes::find($id);
        $data->name = $request->name;
        $data->save();
        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina un registro específico de la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  integer $id ID del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = BudgetFinancementTypes::find($id);
        $data->delete();
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los datos de los tipos de financiamiento
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinancementTypes()
    {
        foreach (BudgetFinancementTypes::all() as $type) {
            $this->data[] = [
                'id' => $type->id,
                'text' => $type->name
            ];
        }
        return response()->json($this->data);
    }
}
