<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\PeriodicCostAttribute;

/**
 * @class PeriodicCostAttributeController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PeriodicCostAttributeController extends Controller
{
    /**
     * Listado de registros de atributos para costos fijos
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo atributo de costo fijo
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Almacena un nuevo atributo de costo fijo
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'sale_type_good_id' => ['required'],
        ]);

        $id = $request->input('periodic_cost_id');

        $attribute = PeriodicCostAttribute::create([
            'name' => $request->input('name'),
            'sale_type_good_id' => $request->input('periodic_cost_id'),
        ]);

        return response()->json(['records' => PeriodicCostAttribute::where('periodic_cost_id', '=', $id)->get()], 200);
    }

    /**
     * Muestra información de un atributo de costo fijo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar un atributo de costo fijo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de un atributo de costo fijo
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'sale_type_good_id' => ['required'],
        ]);

        $id = $request->input('periodic_cost_id');

        $attribute = PeriodicCostAttribute::create([
            'name' => $request->input('name'),
            'periodic_cost_id' => $request->input('periodic_cost_id'),
        ]);

        return response()->json(['records' => PeriodicCostAttribute::where('periodic_cost_id', '=', $id)->get()], 200);
    }

    /**
     * Elimina un atributo de costo fijo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(PeriodicCostAttribute $attribute)
    {
        $attribute->delete();
        return response()->json(['record' => $attribute, 'message' => 'Success'], 200);
    }
}
