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
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetFinancementTypesController extends Controller
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
        $this->middleware('permission:budget.financementtypes.index', ['only' => 'index']);
        $this->middleware('permission:budget.financementtypes.store', ['only' => 'store']);
        $this->middleware('permission:budget.financementtypes.update', ['only' => 'update']);
        $this->middleware('permission:budget.financementtypes.destroy', ['only' => 'destroy']);
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
        return response()->json(['records' => BudgetFinancementTypes::orderBy('id')->get()], 200);
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
        $data = BudgetFinancementTypes::find($id);
        $data->name = $request->name;
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
        $data = BudgetFinancementTypes::find($id);
        $data->delete();
        return response()->json(['record' => $data, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los datos de los tipos de financiamiento
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con listado de los
     * tipos de financiamiento
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
