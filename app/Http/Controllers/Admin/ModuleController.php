<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;

/**
 * @class ModuleController
 * @brief Controlador para la gestión de los módulos de la aplicación
 *
 * Clase que gestiona los módulos de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ModuleController extends Controller
{
    /**
     * Muestra un listado de todos los módulos disponibles
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     View           Devuelve la vista que muestra información de los módulos
     */
    public function index()
    {
        $listModules = info_modules();

        return view('admin.setting-modules', compact('listModules'));
    }

    /**
     * Habilita el módulo seleccionado por el usuario
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request $request Objeto con información de la petición solicitada
     *
     * @return     JsonResponse     Devuelve un JSON con el estatus de la instrucción a ejecutar
     */
    public function enable(Request $request)
    {
        // Objeto con información de un módulo
        $module = Module::findOrFail($request->module);
        $module->enable();

        return response()->json(['result' => true], 200);
    }

    /**
     * Deshabilita el módulo seleccionado por el usuario
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request $request Objeto con información de la petición solicitada
     *
     * @return     JsonResponse     Devuelve un JSON con el estatus de la instrucción a ejecutar
     */
    public function disable(Request $request)
    {
        // Objeto con información de un módulo
        $module = Module::findOrFail($request->module);
        $module->disable();

        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene los detalles de un módulo seleccionado
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request $request Objeto con datos de la petición
     *
     * @return     JsonResponse     Devuelve un JSON con los detalles del módulo
     */
    public function getDetails(Request $request, $module = null)
    {
        if (!$module) {
            $module = $request->module;
        }
        // Objeto con información de un módulo
        $module = Module::find($module);

        if (!$module) {
            return response()->json(['result' => false, 'details' => []], 200);
        }

        // Arreglo con detalles del módulo
        $details = [
            'version' => $module->get("version") ?? "0",
            'name' => $module->get("name_es") ?? $module->getName(),
            'originalName' => $module->getName(),
            'lowerName' => $module->getLowerName(),
            'description' => $module->getDescription() ?? 'N/A',
            'icon' => '',
            'logo' => ($module->get('logo'))
                      ? "assets/" . $module->get('name') . "/images/" . $module->get('logo')
                      : "images/default-avatar.png",
            'status' => '', //Instalado o desinstalado
            'link' => '',
            'authors' => $module->get('authors') ?? [],
            'requirements' => $module->getRequires() ?? [],
            'enabled' => $module->isEnabled()
        ];

        return response()->json(['result' => true, 'details' => $details], 200);
    }

    /**
     * Determina si un módulo está instalado
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $module Nombre del módulo a verificar
     *
     * @return boolean Devuelve verdadero si el módulo está instalado, de lo contrario devuelve falso
     */
    public function checkInstalled($module)
    {
        try {
            return Module::isEnabled($module);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * Deshabilita un módulo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $module Nombre del módulo a deshabilitar
     *
     * @return boolean Devuelve verdadero si el módulo se deshabilitó, de lo contrario devuelve falso
     */
    public function setDisabled($module)
    {
        try {
            return (Module::disable($module));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * Habilita un módulo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $module Nombre del módulo a habilitar
     *
     * @return boolean Devuelve verdadero si el módulo se habilitó, de lo contrario devuelve falso
     */
    public function setEnabled($module)
    {
        try {
            return (Module::enable($module));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
