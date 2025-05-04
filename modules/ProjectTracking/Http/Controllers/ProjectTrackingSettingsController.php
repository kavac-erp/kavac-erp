<?php

/** [descripción del namespace] */

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
 * @brief [controlador dedicado a conectar las distintas funcionalidades para la configuracion de modulo seguimiento]
 *
 * [controlador dedicado a conectar las distintas funcionalidades para la configuracion de modulo seguimiento]
 *
 * @author    [Francisco Escala] [fjescala@gmail.com]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingSettingsController extends Controller
{
    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function index()
    {
        /** @var object Contiene los registros de proyectos */
        $projects = ProjectTrackingProject::all();
        /** @var object Contiene los registros de subproyectos */
        $subprojects = ProjectTrackingSubProject::all();
        /** @var object Contiene los registros de productos */
        $products = ProjectTrackingProduct::all();
        $codeSettings = CodeSetting::where('module', 'projecttracking')->get();
        /** @var object Contiene información sobre la configuración de código para los formularios */
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
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [Oscar González] [xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function store(Request $request)
    {
        /** Reglas de validación para la configuración de códigos */
        $request->validate([
            'projects_code' => [new CodeSettingRule()],
            'sub_projects_code' => [new CodeSettingRule()],
            'products_code' => [new CodeSettingRule()],
            'activity_plans_code' => [new CodeSettingRule()],
        ]);

        /** @var array Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /** @var boolean Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /** @var string Define el modelo al cual hace referencia el código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                if ($table === "projects") {
                    /** @var string Define el modelo para los registros de proyectos */
                    $model = \Modules\ProjectTracking\Models\ProjectTrackingProject::class;
                } elseif ($table === "sub") {
                    /** @var string Define el modelo para los registros de subproyectos */
                    $table = 'sub_projects';
                    $field = 'code';
                    $model = \Modules\ProjectTracking\Models\ProjectTrackingSubProject::class;
                } elseif ($table === "products") {
                    /** @var string Define el modelo para los registros de productos */
                    $model = \Modules\ProjectTracking\Models\ProjectTrackingProduct::class;
                } elseif ($table === "activity") {
                    /** @var string Define el modelo para los registros de plan de actividad */
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

                /** @var boolean Define el estatus verdadero para indicar que se ha registrado información */
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
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function destroy($id)
    {
        //
    }
}
