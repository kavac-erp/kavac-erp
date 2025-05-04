<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstitutionType;
use Illuminate\Http\JsonResponse;

/**
 * @class InstitutionTypeController
 * @brief Gestiona información de los tipos de Organizaciones
 *
 * Controlador para gestionar los tipos de Organizaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InstitutionTypeController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:institution.type.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:institution.type.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:institution.type.delete', ['only' => 'destroy']);
        $this->middleware('permission:institution.type.list', ['only' => 'index']);
    }

    /**
     * Listado de todos los registros de los tipos de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse     JSON con el listado de tipos de organizaciones
     */
    public function index()
    {
        return response()->json(['records' => InstitutionType::all()], 200);
    }

    /**
     * Registra un nuevo tipo de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request    Objeto con información de la petición
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'acronym' => ['required', 'max:4']
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'acronym.required' => 'El campo acrónimo es obligatorio.',
            'acronym.max' => 'El campo acrónimo no debe ser mayor que 4 caracteres.',
        ]);

        if (!restore_record(InstitutionType::class, ['name' => $request->name])) {
            $this->validate($request, [
                'name' => 'unique:institution_types,name'
            ], [
            'name.unique' => 'El nombre ya ha sido registrado.',
            ]);
        }

        // Objeto con información del tipo de organización registrada
        $institutionType = InstitutionType::updateOrCreate([
            'name' => $request->name
        ], [
            'acronym' => ($request->acronym) ? $request->acronym : null
        ]);

        return response()->json(['record' => $institutionType, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información del tipo de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request          $request            Objeto con información de la petición
     * @param  InstitutionType  $institutionType    Objeto con información del tipo de organización a actualizar
     *
     * @return JsonResponse     JSON con información de respuesta a la petición
     */
    public function update(Request $request, InstitutionType $institutionType)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'acronym' => ['max:4']
        ]);

        $institutionType->name = $request->name;
        $institutionType->acronym = ($request->acronym) ? $request->acronym : null;
        $institutionType->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina el tipo de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  InstitutionType  $institutionType    Objeto con información del tipo de organización a eliminar
     *
     * @return JsonResponse     JSON con información sobre la eliminación del tipo de organización
     */
    public function destroy(InstitutionType $institutionType)
    {
        $institutionType->delete();
        return response()->json(['record' => $institutionType, 'message' => 'Success'], 200);
    }

    /**
     * Consulta un tipo específico
     *
     * @author  Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param integer $id ID del tipo
     *
     * @return \Illuminate\Http\JsonResponse con el resultado de la petición
     */
    public function getType(Request $request, $id)
    {
        $type = InstitutionType::find($id);
        return response()->json(['result' => $type], 200);
    }
}
