<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingSubProject
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [Pedro Contreras] [pdrocont@gmail.com]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingSubProjectController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        {
            /** Define las reglas de validación para el formulario */
            $this->validateRules = [
                'project_id'                            => ['required'],
                'name'                                  => ['required'],
                'description'                           => ['nullable', 'max:500'],
                'code'                                  => ['nullable', 'max:20'],
                'responsable_id'                        => ['required'],
                'team'                                  => ['required'],
                'start_date'                            => ['required'],
                'end_date'                              => ['required'],
                'financement_amount'                    => ['required'],
                'currency_id'                           => ['required']
            ];
            }
        /**
         * [descripción del método]
         *
         * @method    index
         *
         * @author    [nombre del autor] [correo del autor]
         *
         * @return    Renderable    [descripción de los datos devueltos]
         */
    }
    public function index()
    {
        $SubprojectsList = ProjectTrackingSubProject::with('Responsable', 'Project')->get()->all();
        foreach ($SubprojectsList as $subproject) {
            $subproject['responsable_name'] = $subproject->Responsable->name;
            $subproject['project_name'] = $subproject->Project->name;
        }
        /** @var condition Condicional que oculta el registro común si existe el módulo de Talento Humano */
        if (Module::has('Payroll')) {
            $payroll = true;
        } else {
            $payroll = false;
        }
        return response()->json(['records' => $SubprojectsList, 'payroll' => $payroll], 200);
    }

    public function getSubprojects()
    {
        $subprojectsList = ProjectTrackingSubProject::all();
        $subprojects = [];
        array_push($subprojects, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($subprojectsList->all() as $subproject) {
            array_push($subprojects, [
                'id' => $subproject->id,
                'text' => $subproject->name,
                'name' => $subproject->name,
                'responsable_id' => $subproject->responsable,
                'start_date' => $subproject->start_date,
                'end_date' => $subproject->end_date
            ]);
        }
        return response()->json($subprojects);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
     * @author    [nombre del autor] [correo del autor]
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
                'project_id'     => ['required', 'max:100'],
                'name'           => ['required', 'max:100', 'unique:project_tracking_sub_projects,name'],
                'code'           => ['nullable', 'max:20'],
                'responsable_id' => ['required', 'max:50'],
                'start_date'     => ['required', 'max:50'],
                'end_date'       => ['required', 'max:50'],
                'financement_amount' => ['required', 'max:500'],
                'currency_id' => ['required']
            ],
            [],
            [
                'project_id'                            => 'Proyecto',
                'name'                                  => 'Nombre',
                'description'                           => 'Descripción',
                'code'                                  => 'Código',
                'responsable_id'                        => 'Responsable del Proyecto',
                'start_date'                            => 'Fecha de Inicio',
                'end_date'                              => 'Fecha Fin',
                'financement_amount'                    => 'Monto de Financiamiento',
                'currency_id'                           => 'Moneda'
            ],
        );

        $codeSetting = CodeSetting::where('table', 'project_tracking_sub_projects')->first();
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
            ProjectTrackingSubProject::class,
            $codeSetting->field
        );

        $projecttrackingSubProject = ProjectTrackingSubProject::create([
            'project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'code' => $code,
            'responsable_id' => $request->responsable_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'financement_amount' => $request->financement_amount,
            'currency_id' => $request->currency_id
        ]);
        return response()->json(['record' => $projecttrackingSubProject, 'message' => 'Success'], 200);
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
     * @return    Renderable    [descripción de los datos devueltos]
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
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request)
    {
        $projecttrackingSubProject = ProjectTrackingSubProject::find($request->input('id'));
        $this->validate(
            $request,
            [
                'project_id'  => ['required', 'max:100'],
                'name'        => ['required', 'max:100', 'unique:project_tracking_sub_projects,name,' . $projecttrackingSubProject->id],
                'description' => ['nullable', 'max:500'],
                'code'        => ['required', 'max:20'],
                'responsable_id' => ['required', 'max:50'],
                'start_date'  => ['required', 'max:50'],
                'end_date'    => ['required', 'max:50'],
                'financement_amount' => ['required', 'max:500'],
                'currency_id' => ['required']
            ],
            [],
            [
                'project_id'                            => 'Proyecto',
                'name'                                  => 'Nombre',
                'description'                           => 'Descripción',
                'code'                                  => 'Código',
                'responsable_id'                        => 'Responsable del Proyecto',
                'start_date'                            => 'Fecha de Inicio',
                'end_date'                              => 'Fecha Fin',
                'financement_amount'                    => 'Monto de Financiamiento',
                'currency_id'                           => 'Moneda'
            ]
        );

        $projecttrackingSubProject->project_id  = $request->project_id;
        $projecttrackingSubProject->name  = $request->name;
        $projecttrackingSubProject->description = $request->description;
        $projecttrackingSubProject->code  = $request->code;
        $projecttrackingSubProject->responsable_id  = $request->responsable_id;
        $projecttrackingSubProject->start_date  = $request->start_date;
        $projecttrackingSubProject->end_date  = $request->end_date;
        $projecttrackingSubProject->financement_amount  = $request->financement_amount;
        $projecttrackingSubProject->currency_id = $request->currency_id;
        $projecttrackingSubProject->save();
        return response()->json(['message' => 'Success'], 200);
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
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $projecttrackingSubProject = ProjectTrackingSubProject::find($id);
        $projecttrackingSubProject->delete();
        return response()->json(['record' => $projecttrackingSubProject, 'message' => 'Success'], 200);
    }
    public function getProjectTrackingSubProject()
    {
        return response()->json(template_choices('Modules\ProjectTracking\Models\ProjectTrackingSubProject', 'name', '', true));
    }

    public function getDetailSubProject($id)
    {
        $SubProject = ProjectTrackingSubProject::with(['Responsable', 'Project'])->find($id);
        return response()->json(['result' => true, 'records' => $SubProject], 200);
    }
}
