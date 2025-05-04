<?php

namespace App\Http\Controllers;

use App\Models\Headquarter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @class HeadquarterController
 * @brief Gestiona información de las sedes
 *
 * Controlador para gestionar las sedes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class HeadquarterController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:headquarter.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:headquarter.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:headquarter.delete', ['only' => 'destroy']);
        $this->middleware('permission:headquarter.list', ['only' => 'index']);
    }

    /**
     * Muesta todos los registros de los sectores de organizaciones
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return JsonResponse     JSON con el listado de sectores de organismos
     */
    public function index()
    {
        return response()->json(['records' => Headquarter::all()], 200);
    }

    /**
     * Registra un nuevo sector de organización
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  Request  $request    Objeto con información de la petición
     *
     * @return JsonResponse         JSON con el resultado del registro para sectores de organismos
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
        ]);

        // Objeto con información de la sede
        $headquarter = Headquarter::create([
            'name' => $request->name
        ]);

        return response()->json(['record' => $headquarter, 'message' => 'success'], 200);
    }

    /**
     * Actualiza la información del sector de organización
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  Request  $request                        Objeto con información de la perición
     * @param  Headquarter  $headquarter    Objeto con información del sector de la organización a actualizar
     *
     * @return JsonResponse     JSON con el resultado de la actualización
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required'],
        ]);

        $headquarter = Headquarter::find($id);

        if (isset($headquarter)) {
            $headquarter->name = $request->name;
            $headquarter->save();
        }

        return response()->json(['message' => 'update'], 200);
    }

    /**
     * Elimina un sector de organización
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  Headquarter  $headquarter    Objeto con información de la sede a eliminar
     *
     * @return JsonResponse     JSON con información del resultado de la eliminación
     */
    public function destroy($id)
    {
        $headquarter = Headquarter::find($id);
        $headquarter->delete();
        return response()->json(['record' => $headquarter, 'message' => 'destroy'], 200);
    }

    /**
     * Muesta todos los registros de los sectores de organizaciones
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return array     JSON con el listado de sectores de organismos
     */
    public function getHeadquarters()
    {
        return template_choices('App\Models\Headquarter', 'name', '', true, null);
    }
}
