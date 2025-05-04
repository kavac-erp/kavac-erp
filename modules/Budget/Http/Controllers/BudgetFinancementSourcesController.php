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
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetFinancementSourcesController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración inicial de la clase.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     */
    public function __construct()
    {
        /**
         * Primer registro para los selects.
         */
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];

        /**
         * Establece permisos de acceso para cada método del controlador
         */
        $this->middleware('permission:budget.financementsources.index', ['only' => 'index']);
        $this->middleware('permission:budget.financementsources.store', ['only' => 'store']);
        $this->middleware('permission:budget.financementsources.update', ['only' => 'update']);
        $this->middleware('permission:budget.financementsources.destroy', ['only' => 'destroy']);
    }

    /**
     * Obtiene un listado de los registros almacenados.
     *
     * @method index
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['records' => BudgetFinancementSources::orderBy('id')->get()], 200);
    }

    /**
     * Almacena un registro recién creado en la base de datos.
     *
     * @method store
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
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
     * Show the specified resource.
     * @return Renderable
     */
    public function show()
    {
    }

    /**
     * Actualiza un registro específico de la base de datos.
     *
     * @method update
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * @method destroy
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con listado de los
     * tipos de financiamiento
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
