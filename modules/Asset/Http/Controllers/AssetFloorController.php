<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Asset\Models\AssetFloor;

/**
 * @class AssetFloorController
 * @brief Clase que maneja los datos asociados a un nivel de edificación
 *
 * Controlador para los niveles de una edificación
 *
 * @author <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetFloorController extends Controller
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
        $this->middleware('permission:asset.setting.floor');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'regex:/^[a-zA-ZÁ-ÿ0-9\s]*$/u', 'max:100'],
            'description' => ['nullable', 'regex:/^[a-zA-ZÁ-ÿ0-9\s]*$/u', 'max:200'],
            'building_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre del nivel es obligatorio.',
            'name.max' => 'El campo nombre del nivel no debe contener mas de 100 caracteres.',
            'name.regex' => 'El campo nombre del nivel no debe contener números ni símbolos.',
            'description.max' => 'El campo descripción del nivel no debe contener mas de 200 caracteres.',
            'description.regex' => 'El campo descripción del nivel no debe contener números ni símbolos.',
            'building_id.required' => 'El campo edificación del nivel es obligatorio.',
        ];
    }

    /**
     * Muestra un listado de los niveles de edificaciones registradas
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        $floors = AssetFloor::with('building')->get();
        return response()->json(['records' => $floors], 200);
    }

    /**
     * Muestra un listado de los niveles registrados de la forma {id, text}
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function getFloors()
    {
        $floors = [];

        foreach (AssetFloor::all() as $floor) {
            $floors[] = [
                'id' => $floor->id,
                'text' => $floor->name,
            ];
        }
        array_unshift($floors, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        return response()->json($floors);
    }

    /**
     * Muestra un listado de los niveles asociados a una edificación registrada de la forma {id, text}
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param $building_id ID de la edificación
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function getBuildingFloors($building_id)
    {
        $floor_options = [];
        $found_floors = AssetFloor::where('building_id', $building_id)->get();
        foreach ($found_floors as $floor) {
            $floor_options[] = [
                'id' => $floor->id,
                'text' => $floor->name,
            ];
        }
        array_unshift($floor_options, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        return response()->json($floor_options);
    }

    /**
     * Valida y registra un nuevo nivel en la tabla asset_floors
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        /* Objeto asociado al modelo AssetFloor */
        $floor = AssetFloor::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'building_id' => $request->building_id,
        ]);
        return response()->json(['record' => $floor, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene detalles de un nivel de edificación
     *
     * @param AssetFloor $floor Nivel de edificación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(AssetFloor $floor): JsonResponse
    {
        return response()->json(['record' => $floor], 200);
    }

    /**
     * Actualiza la informacion asociada a un nivel de edificación
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     \Illuminate\Http\Request         $request     Datos de la petición
     * @param     \Modules\Asset\Models\AssetFloor $floor       Datos de del nivel de edificación que se va a editar
     *
     * @return    \Illuminate\Http\JsonResponse Objeto con la información modificada
     */
    public function update(Request $request, AssetFloor $floor)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $floor->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'building_id' => $request->building_id,
        ]);
        return response()->json(['message' => 'Succes'], 200);
    }

    /**
     * Elimina un nivel de edificacion registrado
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     \Modules\Asset\Models\AssetFloor $floor Datos de un nivel de edificacion
     *
     * @return    \Illuminate\Http\JsonResponse Objeto con la información eliminada
     */
    public function destroy(AssetFloor $floor)
    {
        $floor->delete();
        return response()->json(['record' => $floor, 'message' => 'Succes'], 200);
    }
}
