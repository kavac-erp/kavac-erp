<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Rules\CodeSetting as CodeSettingRule;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingActivity;
use Modules\ProjectTracking\Models\ProjectTrackingActivityPlan;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProjectController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProjectController extends Controller
{
    use ValidatesRequests;

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        /** @var object Contiene los registros del personal, tipos de proyecto y los tipos de producto */
        $ProjectsList = ProjectTrackingProject::with(['Responsable', 'ProjectType', 'TypeProduct', 'Dependency'])->get()->all();
        foreach ($ProjectsList as $project) {
            $project['responsable_name'] = $project->Responsable->name;
            $project['project_type_name'] = $project->ProjectType->name;
            $project['type_product_name'] = $project->TypeProduct ? $project->TypeProduct->name : '';
            $project['dependency_name'] = $project->Dependency->name;
        }
        /** @var condition Condicional que oculta el registro común si existe el módulo de Talento Humano */
        if (Module::has('Payroll')) {
            $payroll = true;
        } else {
            $payroll = false;
        }
        return response()->json(['records' => $ProjectsList, 'payroll' => $payroll], 200);
    }

    /**
     * Retorna un json con todos los Proyectos registrados
     *
     * @method    getProjects
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function getProjects()
    {
        $projectsList = ProjectTrackingProject::all();
        $projects = [];
        array_push($projects, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($projectsList->all() as $project) {
            array_push($projects, [
                'id' => $project->id,
                'text' => $project->name,
                'name' => $project->name,
                'responsable_id' => $project->responsable,
                'dependency_id' => $project->dependency,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
            ]);
        }
        return response()->json($projects);
    }

    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'max:200'],
                'description' => ['nullable', 'max:200'],
                'project_type_id' => ['required'],
                'code' => ['nullable', 'max:100'],
                'dependency_id' => ['required'],
                'responsable_id' => ['required'],
                'type_product_id' => ['nullable'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
                'financing_amount' => ['nullable'],
                'currency_id' => ['required']
            ],
            [],
            [
                'project_type_id' => 'Tipo de proyecto',
                'code' => 'Código',
                'dependency_id' => 'Dependencia',
                'responsable_id' => 'Responsable del proyecto',
                'type_product_id' => 'Tipo de producto',
                'start_date' => 'Fecha de inicio',
                'end_date' => 'Fecha de culminación',
                'financing_amount' => 'Monto de financiamiento',
                'currency_id' => 'Moneda'
            ]
        );

        $codeSetting = CodeSetting::where('table', 'project_tracking_projects')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('projecttracking.setting.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            ProjectTrackingProject::class,
            $codeSetting->field
        );

        $project = ProjectTrackingProject::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'project_type_id' => $request->input('project_type_id'),
            'code' => $code,
            'dependency_id' => $request->input('dependency_id'),
            'responsable_id' => $request->input('responsable_id'),
            'type_product_id' => $request->input('type_product_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'financing_amount' => $request->input('financing_amount'),
            'currency_id' => $request->input('currency_id')
        ]);
        return response()->json(['record' => $project, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function recordInfo($id)
    {
        return response()->json(['records' => ProjectTrackingProject::with(['Responsable', 'ProjectType', 'TypeProduct', 'Dependency'])->find($id)]);
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
     * @return    Renderable    [descripción de los datos devueltos]
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
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'max:200'],
                'description' => ['nullable', 'max:200'],
                'project_type_id' => ['required'],
                'code' => ['nullable', 'max:100'],
                'dependency_id' => ['required'],
                'responsable_id' => ['required'],
                'type_product_id' => ['nullable'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
                'financing_amount' => ['nullable'],
                'currency_id' => ['required']
            ],
            [],
            [
                'project_type_id' => 'Tipo de proyecto',
                'code' => 'Código',
                'dependency_id' => 'Dependencia',
                'responsable_id' => 'Responsable del proyecto',
                'type_product_id' => 'Tipo de producto',
                'start_date' => 'Fecha de inicio',
                'end_date' => 'Fecha de culminación',
                'financing_amount' => 'Monto de financiamiento',
                'currency_id' => 'Moneda'
            ]
        );
        $projects = ProjectTrackingProject::find($request->input('id'));
        $projects->name = $request->input('name');
        $projects->description = $request->input('description');
        $projects->project_type_id = $request->input('project_type_id');
        $projects->code = $request->input('code');
        $projects->dependency_id = $request->input('dependency_id');
        $projects->responsable_id = $request->input('responsable_id');
        $projects->type_product_id = $request->input('type_product_id');
        $projects->start_date = $request->input('start_date');
        $projects->end_date = $request->input('end_date');
        $projects->financing_amount = $request->input('financing_amount');
        $projects->currency_id = $request->input('currency_id');
        $projects->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $project = ProjectTrackingProject::find($id);
        $project->delete();
        return response()->json(['record' => $project, 'message' => 'Success'], 200);
    }
}
