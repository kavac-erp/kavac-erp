<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\HistoryTax;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @class TaxController
 * @brief Gestiona información de Impuestos
 *
 * Controlador para gestionar Impuestos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class TaxController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:tax.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tax.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tax.delete', ['only' => 'destroy']);
        $this->middleware('permission:tax.list', ['only' => 'index']);
    }

    /**
     * Listado de todos los impuestos registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse  JSON con el listado de impuestos
     */
    public function index()
    {

        return response()->json(['records' => Tax::with(['histories' => function ($query) {
            return $query->orderBy('operation_date', 'desc');
        }])->get()], 200);
    }

    /**
     * Registra un nuevo impuesto
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse  JSON con el resultado del registro
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:60|unique:taxes',
            'description' => ['required'],
            'operation_date' => ['required', 'date'],
            'percentage' => ['required']
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe ser mayor que 60 caracteres.',
            'name.unique' => 'El campo nombre ya ha sido registrado.',
            'operation_date.required' => 'El campo fecha entrada en vigencia es obligatorio.',
            'percentage.required' => 'El campo porcentaje es obligatorio.',
        ]);

        // Objeto con información del impuesto registrado
        $tax = Tax::create([
            'name' => $request->name,
            'description' => $request->description,
            'affect_tax' => ($request->affect_tax !== null),
            'active' => ($request->active !== null),
        ]);

        HistoryTax::create([
            'operation_date' => $request->operation_date,
            'percentage' => $request->percentage,
            'tax_id' => $tax->id
        ]);

        return response()->json(['record' => $tax, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de un impuesto
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     * @param     Tax        $tax        Objeto con información del impuesto a actualizar
     *
     * @return    JsonResponse  JSON con el resultado de la actualización
     */
    public function update(Request $request, Tax $tax)
    {
        $this->validate($request, [
            'name' => ['required', 'max:60'],
            'description' => ['required'],
            'operation_date' => ['required', 'date'],
            'percentage' => ['required']
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe ser mayor que 60 caracteres.',
            'name.unique' => 'El campo nombre ya ha sido registrado.',
            'operation_date.required' => 'El campo fecha entrada en vigencia es obligatorio.',
            'percentage.required' => 'El campo porcentaje es obligatorio.',
        ]);

        $tax->name = $request->name;
        $tax->description = $request->description;
        $tax->affect_tax = ($request->affect_tax) ? true : false;
        $tax->active = ($request->active) ? true : false;
        $tax->save();

        $tax->histories()->create([
            'operation_date' => $request->operation_date,
            'percentage' => $request->percentage
        ]);

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina un impuesto
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Tax        $tax    Objeto con información del impuesto a eliminar
     *
     * @return    JsonResponse  JSON con el resultado de la eliminación
     */
    public function destroy(Tax $tax)
    {
        $tax->delete();
        return response()->json(['record' => $tax, 'message' => 'Success'], 200);
    }

    /**
     * Listado de impuestos registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse    Objeto con información de los impuestos
     */
    public function getAll()
    {
        $taxes = Tax::with(['histories' => function ($q) {
            $q->orderBy('operation_date', 'desc');
        }])->where('active', true)->get();
        return response()->json(['records' => $taxes]);
    }

    /**
     * Listado de impuestos registrados para select de vue
     *
     * @author     Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    JsonResponse    Objeto con información de los impuestos
     */
    public function getTaxesVueSelect()
    {
        return response()->json(['records' => template_choices('App\Models\Tax', 'name', [], true)]);
    }

    /**
     * Listado de impuestos registrados para select de vue
     *
     * @author     Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    JsonResponse    Objeto con información de los impuestos
     */
    public function getLastHistoryTax($tax_id)
    {
        return response()->json([
            'record' => HistoryTax::where('tax_id', $tax_id)
                        ->orderBy('operation_date', 'DESC')->first()
        ]);
    }
}
