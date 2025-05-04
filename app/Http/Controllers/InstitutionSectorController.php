<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstitutionSector;
use Illuminate\Http\JsonResponse;

/**
 * @class InstitutionSectorController
 * @brief Gestiona información de los sectores de Organizaciones
 *
 * Controlador para gestionar sectores de Organizaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InstitutionSectorController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:institution.sector.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:institution.sector.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:institution.sector.delete', ['only' => 'destroy']);
        $this->middleware('permission:institution.sector.list', ['only' => 'index']);
    }

    /**
     * Muesta todos los registros de los sectores de organizaciones
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse     JSON con el listado de sectores de organismos
     */
    public function index()
    {
        return response()->json(['records' => InstitutionSector::all()], 200);
    }

    /**
     * Registra un nuevo sector de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request    Objeto con información de la petición
     *
     * @return JsonResponse         JSON con el resultado del registro para sectores de organismos
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
        ]);

        if (!restore_record(InstitutionSector::class, ['name' => $request->name])) {
            $this->validate($request, [
                'name' => ['unique:institution_sectors,name']
            ]);
        }

        // Objeto con información del sector de organizaciones
        $institutionSector = InstitutionSector::updateOrCreate([
            'name' => $request->name
        ]);

        return response()->json(['record' => $institutionSector, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información del sector de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request  $request                        Objeto con información de la perición
     * @param  InstitutionSector  $institutionSector    Objeto con información del sector de la organización a actualizar
     *
     * @return JsonResponse     JSON con el resultado de la actualización
     */
    public function update(Request $request, InstitutionSector $institutionSector)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
        ]);

        $institutionSector->name = $request->name;
        $institutionSector->save();

        return response()->json(['message' => __('Registro actualizado correctamente')], 200);
    }

    /**
     * Elimina un sector de organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  InstitutionSector  $institutionSector    Objeto con información del sector organizaciones a eliminar
     *
     * @return JsonResponse     JSON con información del resultado de la eliminación
     */
    public function destroy(InstitutionSector $institutionSector)
    {
        $institutionSector->delete();
        return response()->json(['record' => $institutionSector, 'message' => 'Success'], 200);
    }

    /**
     * Consulta un sector específico
     *
     * @author  Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param $id ID del sector
     *
     * @return \Illuminate\Http\JsonResponse con el resultado de la petición
     */
    public function getSector(Request $request, $id)
    {
        $sector = InstitutionSector::find($id);
        return response()->json(['result' => $sector], 200);
    }
}
