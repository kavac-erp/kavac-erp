<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeasurementUnit;
use Illuminate\Http\JsonResponse;

/**
 * @class MeasurementUnitController
 * @brief Gestiona información de las unidades de medida
 *
 * Controlador para gestionar unidades de medida
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class MeasurementUnitController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:measurement.units.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:measurement.units.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:measurement.units.delete', ['only' => 'destroy']);
        $this->middleware('permission:measurement.units.list', ['only' => 'index']);
    }

    /**
     * Listado de todas las unidades de medida registradas
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse    JSON con las unidades de medida registradas
     */
    public function index()
    {
        return response()->json(['records' => MeasurementUnit::all()], 200);
    }

    /**
     * Registra una nueva unidad de medida
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return    JsonResponse     JSON con información del registro de la unidad de medida
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:measurement_units,name'],
            'description' => ['required'],
            'acronym' => ['required', 'max:6', 'unique:measurement_units,acronym']
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.unique' => 'El campo nombre El nombre ya ha sido registrado.',
            'description.required' => 'El campo descripción es obligatorio.',
            'acronym.required' => 'El campo acrónimo es obligatorio.',
            'acronym.max' => 'El campo acrónimo no debe ser mayor que 6 caracteres.',
            'acronym.unique' => 'El campo acrónimo ya ha sido registrado.',
        ]);

        // Objeto con información de la unidad de medida registrada
        $measurementUnit = MeasurementUnit::create([
            'name' => $request->name,
            'description' => $request->description,
            'acronym' => $request->acronym
        ]);

        return response()->json(['record' => $measurementUnit, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de una unidad de medida
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request            $request            Objeto con información de la petición
     * @param     MeasurementUnit    $measurementUnit    Objeto con información de la unidad de medida a actualizar
     *
     * @return    JsonResponse       JSON con información sobre la actualización de la unidad de medida
     */
    public function update(Request $request, MeasurementUnit $measurementUnit)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:measurement_units,name,' . $measurementUnit->id],
            'description' => ['required'],
            'acronym' => ['required', 'max:6', 'unique:measurement_units,acronym,' . $measurementUnit->id]
        ]);

        $measurementUnit->name = $request->name;
        $measurementUnit->description = $request->description;
        $measurementUnit->acronym = $request->acronym;
        $measurementUnit->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina una unidad de medida
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     MeasurementUnit    $measurementUnit    Objeto con información de la unidad de medida a eliminar
     *
     * @return    JsonResponse       JSON con información sobre la eliminación de la unidad de medida
     */
    public function destroy(MeasurementUnit $measurementUnit)
    {
        $measurementUnit->delete();
        return response()->json(['record' => $measurementUnit, 'message' => 'Success'], 200);
    }
}
