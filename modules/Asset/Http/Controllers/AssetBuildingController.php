<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetBuilding;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AssetBuildingController
 * @brief Clase que maneja los datos asociados a una edificación
 *
 * Clase que maneja los datos de una edificación
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetBuildingController extends Controller
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
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:asset.setting.building');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'regex:/^[a-zA-ZÁ-ÿ0-9\s]*$/u', 'max:100'],
            'description' => ['nullable', 'regex:/^[a-zA-ZÁ-ÿ0-9\s]*$/u', 'max:200'],
            'institution_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre de la edificación es obligatorio.',
            'name.max' => 'El campo nombre de la edificación no debe contener mas de 100 caracteres.',
            'name.regex' => 'El campo nombre de la edificación no debe contener números ni símbolos.',
            'description.max' => 'El campo descripción de la edificación no debe contener mas de 200 caracteres.',
            'description.regex' => 'El campo descripción de la edificación no debe contener números ni símbolos.',
            'institution_id.required' => 'El campo organización es obligatorio',
        ];
    }

    /**
     * Muestra un listado de las edificaciones registradas
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetBuilding::all()], 200);
    }

    /**
     * Muestra un listado de las edificaciones registradas de la forma {id, text}
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function getBuildings()
    {
        $buildings = [];
        foreach (AssetBuilding::all() as $building) {
            $buildings[] = [
                'id' => $building->id,
                'text' => $building->name,
            ];
        }
        array_unshift($buildings, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        return response()->json($buildings);
    }

    /**
     * Valida y registra una nueva edificacion en la tabla asset_buildings
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        /* Objeto asociado al modelo AssetBuilding */
        $building = AssetBuilding::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'institution_id' => $request->institution_id,
        ]);
        return response()->json(['record' => $building, 'message' => 'Succes'], 200);
    }

    /**
     * Actualiza la informacion asociada a una edificación
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request       $request  Datos de la petición
     * @param     AssetBuilding $building Datos de la edificación que se va a editar
     *
     * @return    JsonResponse Objeto con la información modificada
     */
    public function update(Request $request, AssetBuilding $building)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $building->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'institution_id' => $request->institution_id,
        ]);
        return response()->json(['message' => 'Succes'], 200);
    }

    /**
     * Elimina una edificacion registrada
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     AssetBuilding $building Datos de una edificacion
     *
     * @return    JsonResponse Objeto con la información eliminada
     */
    public function destroy(AssetBuilding $building)
    {
        $building->delete();
        return response()->json(['record' => $building, 'message' => 'Succes'], 200);
    }
}
