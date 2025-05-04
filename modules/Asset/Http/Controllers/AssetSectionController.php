<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetSection;
use Modules\Asset\Models\SectionAmount;

/**
 * @class AssetSectionController
 * @brief Clase que maneja los datos asociados a una seccion de edificación
 *
 * Controlador para las secciones de una edificación
 *
 * @author <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSectionController extends Controller
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
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:asset.setting.section');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'regex:/^[a-zA-ZÁ-ÿ0-9\s\-]*$/u', 'max:100'],
            'description' => ['nullable', 'regex:/^[a-zA-ZÁ-ÿ0-9\s]*$/u', 'max:200'],
            'building_id' => ['required'],
            'floor_id' => ['required'],
            'section_id' => '',
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre de la seccion es obligatorio.',
            'name.max' => 'El campo nombre de la seccion no debe contener mas de 100 caracteres.',
            'name.regex' => 'El campo nombre de la seccion no debe contener números ni símbolos.',
            'description.max' => 'El campo descripción de la seccion no debe contener mas de 200 caracteres.',
            'description.regex' => 'El campo descripción de la seccion no debe contener números ni símbolos.',
            'building_id.required' => 'El campo edificación de la seccion es obligatorio.',
            'floor_id.required' => 'El campo nivel de la seccion es obligatorio.',
        ];
    }

    /**
     * Muestra un listado de las secciones de edificaciones registradas
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        $sections = AssetSection::with(['building', 'floor'])->get();
        return response()->json(['records' => $sections], 200);
    }

    /**
     * Muestra un listado de las secciones asociadas a un nivel de una edificación registrada de la forma {id, text}
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param $floor_id ID del nivel de la edificación
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function getFloorSections($floor_id)
    {
        $section_options = [];
        $found_sections = AssetSection::where('floor_id', $floor_id)->get();
        foreach ($found_sections as $section) {
            $section_options[] = [
                'id' => $section->id,
                'text' => $section->name,
            ];
        }
        array_unshift($section_options, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        return response()->json($section_options);
    }

    /**
     * Valida y registra una nueva seccion en la tabla asset_sections
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
        $sectionAmountInfo = SectionAmount::where('code', $request->name)->first();

        if (is_null($sectionAmountInfo)) {
            $sectionAmountInfo = SectionAmount::create([
                'code' => $request->name,
            ]);
        }
        if ($request->office_amount > 1) {
            $sectionAmountInfo->amount += $request->office_amount;
            $sectionAmountInfo->save();

            foreach ($request->section_registers as $section) {
                $current_section = AssetSection::create([
                    'name' => $section['name'],
                    'description' => $section['description'],
                    'building_id' => $request->building_id,
                    'floor_id' => $request->floor_id,
                ]);
            }

            return response()->json(['message' => 'Success'], 201);
        }

        $sectionAmountInfo->amount += 1;
        $sectionAmountInfo->save();
        $section = AssetSection::create([
            'name' => $request->name . '-' . $sectionAmountInfo->amount,
            'description' => $request->description,
            'building_id' => $request->building_id,
            'floor_id' => $request->floor_id,
        ]);

        return response()->json(['record' => $section, 'message' => 'Success'], 201);
    }

    /**
     * Obtiene el monto de la sección
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Objeto con los datos de la petición
     * @param mixed $code código de la sección
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSectionAmount(Request $request, $code)
    {
        $sectionAmountInfo = SectionAmount::where('code', $code)->first();
        if (is_null($sectionAmountInfo)) {
            return response()->json(['amount' => 0], 200);
        }
        return response()->json(['amount' => $sectionAmountInfo->amount], 200);
    }

    /**
     * Actualiza la informacion asociada a una sección de edificación
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     \Illuminate\Http\Request                      $request             Datos de la petición
     * @param     \Modules\Asset\Models\AssetSection $building Datos de la seccion que se va a editar
     *
     * @return    \Illuminate\Http\JsonResponse Objeto con la información modificada
     */
    public function update(Request $request, AssetSection $section)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $section->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'building_id' => $request->building_id,
            'floor_id' => $request->floor_id,
        ]);
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Valida si no hay asignaciones asociadas a la sección actual, y si no tiene secciones, la elimina
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     \Modules\Asset\Models\AssetSection  $section Datos de una sección
     *
     * @return    \Illuminate\Http\JsonResponse Objeto con la información eliminada
     */
    public function destroy(Request $request, AssetSection $section)
    {
        if (isset($section->asignations)) {
            $errors['error'][0] = 'No se puede eliminar una sección que ya ha sido asignada.';
            return response()->json([
                'message' => 'No se puede eliminar una sección que ya ha sido asignada.', 'error' => $errors
            ], 200);
        }
        $section->delete();
        return response()->json(['record' => $section, 'message' => 'Success'], 200);
    }
}
