<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Accounting\Models\AccountingEntryCategory;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AccountingEntryCategoryController
 * @brief Controlador para la gestion de las categorias de asientos contables
 *
 * Clase que gestiona las categorias de asientos contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingEntryCategoryController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:accounting.setting.category.store', ['only' => ['store']]);
        $this->middleware('permission:accounting.setting.category.update', ['only' => ['update']]);
        $this->middleware('permission:accounting.setting.category.delete', ['only' => 'destroy']);
    }

    /**
     * Listado de categorías de cuentas contables
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => AccountingEntryCategory::orderBy('name')->get()], 200);
    }

    /**
     * Crea una nueva categorias de origen de asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>

     * @param  Request $request Datos de la petición realizada

     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'    => ['required', 'string'],
            'acronym' => ['required', 'string'],
        ]);

        $institution = get_institution();

        /* almacenar la información para el nuevo registro */
        AccountingEntryCategory::create([
                                        'name'           => $request->name,
                                        'acronym'        => $request->acronym,
                                        'institution_id' => $institution->id,
                                    ]);

        return response()->json([
            'records' => AccountingEntryCategory::orderBy('name')->get(), 'message' => 'Success'
        ], 200);
    }

    /**
     * Actualiza los datos de la categoria de origen
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>

     * @param  Request $request Datos de la petición realizada
     * @param  integer $id      Identificador de la categoria de origen a modificar

     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'    => ['required', 'string'],
            'acronym' => ['required', 'string'],
        ]);
        /* contine el registro de conversión a editar */
        $record          = AccountingEntryCategory::find($id);
        $record->name    = $request['name'];
        $record->acronym = $request['acronym'];
        $record->save() ;

        return response()->json([
            'records' => AccountingEntryCategory::orderBy('name')->get(),
            'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina una categoria de origen de asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>

     * @param  integer $id Identificador de la categoria de origen a eliminar

     * @return JsonResponse
     */
    public function destroy($id)
    {
        $category = AccountingEntryCategory::with('accountingEntries')->find($id);
        if ($category) {
            /* validar si no esta relacionada con algun asiento es permitido eliminarla */
            if (count($category->accountingEntries) > 0) {
                return response()->json([
                    'error'   => true,
                    'message' => 'El registro no se puede eliminar, debido a que existen asientos relacionados.'
                ], 200);
            }
            $category->delete();
        }
        return response()->json([
            'record'  => $category,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene los datos de las entidades bancarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>

     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con listado de las entidades bancarias
     */
    public function getCategories()
    {
        $records = [];
        foreach (AccountingEntryCategory::all() as $category) {
            $records[] = [
                'id'      => $category->id,
                'text'    => $category->name,
                'acronym' => $category->acronym,
            ];
        }

        return response()->json($records);
    }
}
