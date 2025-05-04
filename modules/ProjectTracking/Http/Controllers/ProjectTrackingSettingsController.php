<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\CodeSetting;
use App\Rules\CodeSetting as CodeSettingRule;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;

/**
 * @class ProjectTrackingSettingsController
 * @brief Controlador dedicado a conectar las distintas funcionalidades para la configuracion de modulo seguimiento
 *
 * Controlador dedicado a conectar las distintas funcionalidades para la configuracion de modulo seguimiento
 *
 * @author    Francisco Escala <fjescala@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingSettingsController extends Controller
{
    /**
     * Configuración general del módulo de seguimiento de proyectos
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        /* Contiene los registros de proyectos */
        $projects = ProjectTrackingProject::all();
        /* Contiene los registros de subproyectos */
        $subprojects = ProjectTrackingSubProject::all();
        /* Contiene los registros de productos */
        $products = ProjectTrackingProduct::all();
        $codeSettings = CodeSetting::where('module', 'projecttracking')->get();
        /* Contiene información sobre la configuración de código para los formularios */
        $pjCode = $codeSettings->where('table', 'project_tracking_projects')->first();
        $spCode = $codeSettings->where('table', 'project_tracking_sub_projects')->first();
        $pdCode = $codeSettings->where('table', 'project_tracking_products')->first();
        $paCode = $codeSettings->where('table', 'project_tracking_activity_plans')->first();

        return view('projecttracking::settings', compact(
            'pjCode',
            'spCode',
            'pdCode',
            'paCode',
        ));
    }

    /**
     * Muestra el formulario de configuración del módulo de seguimiento de proyectos
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena la configuración del módulo de seguimiento de proyectos
     *
     * @author    Oscar González [xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve]
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /* Reglas de validación para la configuración de códigos */
        $request->validate([
            'projects_code' => [new CodeSettingRule()],
            'sub_projects_code' => [new CodeSettingRule()],
            'products_code' => [new CodeSettingRule()],
            'activity_plans_code' => [new CodeSettingRule()],
        ]);

        /* Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /* Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /* Define el modelo al cual hace referencia el código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                if ($table === "projects") {
                    /* Define el modelo para los registros de proyectos */
                    $model = ProjectTrackingProject::class;
                } elseif ($table === "sub") {
                    /* Define el modelo para los registros de subproyectos */
                    $table = 'sub_projects';
                    $field = 'code';
                    $model = ProjectTrackingSubProject::class;
                } elseif ($table === "products") {
                    /* Define el modelo para los registros de productos */
                    $model = ProjectTrackingProduct::class;
                } elseif ($table === "activity") {
                    /* Define el modelo para los registros de plan de actividad */
                    $table = 'activity_plans';
                    $field = 'code';
                    $model = \Modules\ProjectTracking\Models\ProjectTrackingActivityPlan::class;
                }

                $codeSetting = CodeSetting::where([
                    'module' => 'projecttracking',
                    'table'  => 'project_tracking_' . $table,
                    'field'  => $field,
                ])->first();

                if (!isset($codeSetting)) {
                    $codeSetting = CodeSetting::create([
                        'module'        => 'projecttracking',
                        'table'         => 'project_tracking_' . $table,
                        'field'         => $field,
                        'format_prefix' => $prefix,
                        'format_digits' => $digits,
                        'format_year'   => $sufix,
                        'model'         => $model
                    ]);
                }

                /* Define el estatus verdadero para indicar que se ha registrado información */
                $saved = true;
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        } else {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'No se registro ningún cambio'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Muestra información de la configuración
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario de edición de la configuración
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza la configuración
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina la configuración
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function destroy($id)
    {
        //
    }
}
