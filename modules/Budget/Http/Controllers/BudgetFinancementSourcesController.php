<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\BudgetFinancementSources;

/**
 * @class BudgetFinancementSourcesController
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
class BudgetFinancementSourcesController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de fuentes de financiamiento
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
        $this->middleware('permission:budget.financementsources.index', ['only' => 'index']);
        $this->middleware('permission:budget.financementsources.store', ['only' => 'store']);
        $this->middleware('permission:budget.financementsources.update', ['only' => 'update']);
        $this->middleware('permission:budget.financementsources.destroy', ['only' => 'destroy']);
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
        return response()->json(['records' => BudgetFinancementSources::orderBy('id')->get()], 200);
    }

    /**
     * Almacena un registro recién creado en la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required','unique:budget_financement_sources'],
                'budget_financement_type_id' => ['required'],
            ],
            [
                'name.required' => 'El nombre del tipo de financiamiento es obligatorio.',
                'name.unique' => 'El nombre del tipo de financiamiento ya ha sido registrado.',
                'budget_financement_type_id.required' => 'La fuente de financiamiento es obligatoria.',
            ]
        );

        $data = DB::transaction(function () use ($request) {
            $data = BudgetFinancementSources::create([
                'budget_financement_type_id' => $request->budget_financement_type_id,
                'name' => $request->name
            ]);
            return $data;
        });
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los detalles de una fuente de financiamiento
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
     * @param  integer $id         ID de la fuente de financiamiento
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = BudgetFinancementSources::find($id);
        $data->name = $request->name;
        $data->budget_financement_type_id = $request->budget_financement_type_id;
        $data->save();
        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina un registro específico de la base de datos.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  integer $id ID de la fuente de financiamiento
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $data = BudgetFinancementSources::find($id);
        $data->delete();
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los datos de las fuentes de financiamiento
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinancementSources($financement_type_id = null)
    {
        $financementSources = ($financement_type_id)
            ? BudgetFinancementSources::where('budget_financement_type_id', $financement_type_id)->get()
            : BudgetFinancementSources::all();

        foreach ($financementSources as $financementSource) {
            $this->data[] = [
                'id' => $financementSource->id,
                'text' => $financementSource->name
            ];
        }

        return response()->json($this->data);
    }
}
