<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Warehouse\Models\WarehouseClose;
use Modules\Warehouse\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

/**
 * @class WarehouseCloseController
 * @brief Controlador de los cierres de almacén registrados
 *
 * Clase que gestiona los cierres de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseCloseController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'warehouse_id' => ['required'],
            'initial_date' => ['required'],
            'end_date'     => ['nullable', 'after_or_equal:initial_date'],
            'observations' => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'warehouse_id.required' => 'El campo nombre del almacén es obligatorio.',
            'initial_date.required' => 'El campo inicio del cierre de almacén es obligatorio.',
            'end_date.after_or_equal' => 'Fin de cierre de almacén debe ser una fecha posterior o igual a inicio del cierre de almacén',
            'observations.required' => 'El campo observaciones del cierre de almacén es obligatorio.'
        ];
    }

    /**
     * Muestra un listado de los cierres de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(
            ['records' => WarehouseClose::with('initialUser', 'endUser', 'warehouse')->get()],
            200
        );
    }

    /**
     * Valida y registra un nuevo cierre de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $warehouse = Warehouse::find($request->warehouse_id);
        $hoy = date("Y-m-d");
        if ($request->initial_date <= $hoy) {
            $warehouse->active = false;
            $warehouse->save();
        }

        $close = WarehouseClose::create([
            'initial_date'    => $request->input('initial_date'),
            'end_date'        => $request->input('end_date'),
            'observations'    => $request->input('observations'),
            'warehouse_id'    => $request->input('warehouse_id'),
            'initial_user_id' => Auth::id(),
        ]);
        if (!is_null($request->end_date)) {
            $close->end_user_id = Auth::id();
            $close->save();
        }
        return response()->json(['record' => $close, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de los cierres de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  \Modules\Warehouse\Models\WarehouseClose  $close Datos del cierre de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WarehouseClose $close)
    {
        $this->validate($request, [
            'initial_date'  => ['required'],
            'observations'  => ['required'],
            'warehouse_id'  => ['required'],
        ]);

        if ($close->initial_user_id == Auth::id()) {
            $close->initial_date = $request->input('initial_date');
            $close->observations = $request->input('observations');
            $close->warehouse_id = $request->input('warehouse_id');

            if ($close->end_user_id == $close->initial_user_id) {
                $close->end_date = $request->input('end_date');
            }
        } elseif (
            ($close->end_user_id == Auth::id()) ||
            (is_null($close->end_user_id) && ($close->initial_user_id == Auth::id()))
        ) {
            $close->observations = $request->input('observations');
            $close->end_date     = $request->input('end_date');
        }
        $close->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un cierre de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Modules\Warehouse\Models\WarehouseClose $close Datos del Cierre de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(WarehouseClose $close)
    {
        $close->delete();
        return response()->json(['record' => $close, 'message' => 'Success'], 200);
    }

    /**
     * Finaliza un cierre de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del Cierre de Almacén
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeWarehouse($id)
    {
        $close = WarehouseClose::find($id);
        if (!is_null($close)) {
            $close->end_user_id = Auth::id();
            $close->end_date = now();
            $close->save();

            $warehouse = Warehouse::find($close->warehouse_id);
            $warehouse->active = true;
            $warehouse->save();
        }
        return response()->json(
            ['records' => WarehouseClose::with('initialUser', 'endUser', 'warehouse')->get()],
            200
        );
    }
}
