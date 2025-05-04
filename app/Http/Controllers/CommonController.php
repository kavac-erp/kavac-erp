<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @class CommonController
 * @brief Gestiona información común de la aplicación
 *
 * Controlador para gestionar datos comúnes en la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CommonController extends Controller
{
    /**
     * Obtiene Datos de modelos relacionados
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request          $request         Datos de la petición
     * @param     string           $parent_model    Nombre del modelo padre
     * @param     integer          $parent_id       Identificador del elemento relacionado
     * @param     string           $model           Nombre del modelo
     * @param     string           $module_name     Nombre del módulo
     * @param     string           $fk              Clave foránea
     *
     * @return    JsonResponse     Datos con los registros relacionados
     */
    public function getSelectData(Request $request, $parent_model, $parent_id, $model, $module_name = null, $fk = null)
    {
        $model_name = ($model == 'User')
                      ? "App\\Models\\{$model}"
                      : ((!is_null($module_name)) ? "Modules\\{$module_name}" : 'App') . "\\Models\\{$model}";

        $fk = (is_null($fk))
              ? ((strpos($parent_model, '_id') === false)
              ? strtolower($parent_model) . '_id'
              : $parent_model)
              : $fk;

        return response()->json([
            'result' => true, 'records' => $model_name::where($fk, $parent_id)->orderBy('name')->get()
        ], 200);
    }

    /**
     * Obtiene Datos de modelos relacionos pero para tablas que no poseen campo name
     *
     * @author Ing. Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @param     Request          $request         Datos de la petición
     * @param     string           $parent_model    Nombre del modelo padre
     * @param     integer          $parent_id       Identificador del elemento relacionado
     * @param     string           $model           Nombre del modelo
     * @param     string           $module_name     Nombre del módulo
     * @param     string           $fk              Clave foránea
     *
     * @return    JsonResponse     Datos con los registros relacionados
     */
    public function getSelectDataCustom(Request $request, $parent_model, $parent_id, $model, $module_name = null, $fk = null)
    {
        $model_name = ($model == 'User')
                      ? "App\\Models\\{$model}"
                      : ((!is_null($module_name)) ? "Modules\\{$module_name}" : 'App') . "\\Models\\{$model}";

        $fk = (is_null($fk))
              ? ((strpos($parent_model, '_id') === false)
              ? strtolower($parent_model) . '_id'
              : $parent_model)
              : $fk;

        return response()->json([
            'result' => true, 'records' => $model_name::where($fk, $parent_id)->get()
        ], 200);
    }

    /**
     * Obtiene Datos de modelos relacionados que tengan estatus activo
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request          $request         Datos de la petición
     * @param     string           $parent_model    Nombre del modelo padre
     * @param     integer          $parent_id       Identificador del elemento relacionado
     * @param     string           $model           Nombre del modelo
     * @param     string           $module_name     Nombre del módulo
     * @param     string           $fk              Clave foránea
     *
     * @return    JsonResponse     Datos con los registros relacionados
     */
    public function getSelectActive(Request $request, $parent_model, $parent_id, $model, $module_name = null, $fk = null)
    {
        $model_name = ($model == 'User')
                      ? "App\\Models\\{$model}"
                      : ((!is_null($module_name)) ? "Modules\\{$module_name}" : 'App') . "\\Models\\{$model}";



        if (is_null($fk)) {
            return response()->json([
                'result' => true,
                'records' => $model_name::where([
                    ["active", true],
                    [$parent_model,$parent_id]
                ])->get(),
            ], 200);
        } else {
            return response()->json([
                'result' => true,
                'records' => $model_name::with([$fk])->where([
                    ["active", true],
                    [$parent_model, $parent_id]
                ])->get(),
            ], 200);
        }
    }

    /**
     * Obtiene Datos del los perfiles relacionados al campo seleccionado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     Request          $request         Datos de la petición
     * @param     string           $parent_model    Nombre del modelo padre
     * @param     integer          $parent_id       Identificador del elemento relacionado
     * @param     string           $fk              Clave foránea
     *
     * @return    JsonResponse     Datos con los registros relacionados
     */
    public function getSelectStaffData(Request $request, $parent_model, $parent_id, $fk = null)
    {
        $model_name = "App\\Models\\Profile";

        $fk = (is_null($fk))
              ? ((strpos($parent_model, '_id') === false)
              ? strtolower($parent_model) . '_id'
              : $parent_model)
              : $fk;

        return response()->json([
            'result' => true,
            'records' => $model_name::where($fk, $parent_id)->where('user_id', null)->orderBy('first_name')->get()
        ], 200);
    }

    /**
     * Determina si un registro se encuentra eliminado
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request      $request    Datos de la petición
     *
     * @return    JsonResponse  Datos con el resultado de la verificación.
     *                          Devuelve verdadero si el registro se encuentra eliminado,
     *                          de lo contrario retorna falso
     */
    public function isDeleted(Request $request)
    {
        $model = ucfirst(Str::camel($request->source));
        $filters = $request->filters;
        $record = $model::onlyTrashed()->where($filters)->first();

        return response()->json(['record' => $record], 200);
    }
}
