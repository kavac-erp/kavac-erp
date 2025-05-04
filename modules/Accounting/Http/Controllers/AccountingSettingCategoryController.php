<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Accounting\Models\AccountingEntryCategory;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AccountingSettingCategoryController
 * @brief Controlador para la gestion las categorias de origen
 *
 * Clase que gestiona las categorias de origen
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingSettingCategoryController extends Controller
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

    public function index()
    {
        return response()->json(['records' => AccountingEntryCategory::orderBy('name')->get()], 200);
    }

    /**
     * Crea una nueva categorias de origen de asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'    => ['required', 'string'],
            'acronym' => ['required', 'string'],
        ]);

        AccountingEntryCategory::create($request->all());

        return response()->json([
            'records' => AccountingEntryCategory::orderBy('name')->get(), 'message' => 'Success'
        ], 200);
    }

    /**
     * Actualiza los datos de la categoria de origen
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     * @param  integer $id      Identificador de la categoria de origen a modificar
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'    => ['required', 'string'],
            'acronym' => ['required', 'string'],
        ]);
        /* registro de conversión a editar */
        $record          = AccountingEntryCategory::find($id);
        $record->name    = $request['name'];
        $record->acronym = $request['acronym'];
        $record->save() ;

        return response()->json([
            'records' => AccountingEntryCategory::orderBy('name')->get(), 'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina una categoria de origen de asiento contable
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id Identificador de la categoria de origen a eliminar
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $category = AccountingEntryCategory::with('accountingEntries')->find($id);

        if ($category) {
            /* validar si no esta relacionada con algun asiento es permitido eliminarla */
            if (!is_null($category->accountingEntries) && count($category->accountingEntries) > 0) {
                return response()->json([
                    'error'   => true,
                    'message' => 'El registro no se puede eliminar, debido a que existen asientos contables que la usan.'
                ], 200);
            }
            if ($category->acronym == 'ARA') {
                return response()->json([
                    'error'   => true,
                    'message' => 'El registro no se puede eliminar, debido a que es una categoría predefinida.'
                ], 200);
            }
            $category->delete();
        }
        return response()->json(['record' => $category, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los datos de las categorías
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse Devuelve un JSON con listado de las categorías
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
