<?php

namespace Modules\ProjectTracking\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingSubProject
 * @brief Gestiona los procesos del controlador
 *
 * @author Pedro Contreras <pdrocont@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingSubProjectController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'project_id'         => ['required'],
            'name'               => [
                'required',
                'unique:Modules\ProjectTracking\Models\ProjectTrackingSubProject,name',
                'max:100',
            ],
            'description'        => ['nullable', 'max:500'],
            'code'               => ['nullable', 'max:20'],
            'responsable_id'     => ['required', 'max:50'],
            'product_types'      => ['required', 'array', 'min:1'],
            'start_date'         => ['required'],
            'end_date'           => ['required'],
            'financement_amount' => ['required', 'max:500'],
            'currency_id'        => ['required'],
        ];

        /* Define los mensajes para las reglas de validación */
        $this->messages = [
            'project_id.required'         => 'El campo Proyecto es obligatorio.',
            'name.required'               => 'El campo Nombre es obligatorio.',
            'name.unique'                 => 'El campo Nombre ya ha sido registrado.',
            'name.max'                    => 'El campo Nombre no debe contener mas de 100 caracteres',
            'description.max'             => 'El campo Descripción no debe contener mas de 500 caracteres.',
            'code.max'                    => 'El campo Código no debe contener mas de 20 caracteres.',
            'responsable_id.required'     => 'El campo Responsable del proyecto es obligatorio.',
            'start_date.required'         => 'El campo Fecha de Inicio es obligatorio.',
            'start_date.before_or_equal'  => 'La fecha de inicio no puede ser posterior a la fecha Fin.',
            'end_date.required'           => 'El campo Fecha Fin es obligatorio.',
            'end_date.after_or_equal'     => 'La fecha Fin no puede ser anterior a la fecha de inicio.',
            'currency_id.required'        => 'El campo Moneda es obligatorio.',
            'financement_amount.required' => 'El campo Monto de Financiamiento es obligatorio.',
            'product_types.required'      => 'El campo Tipos de producto es obligatorio.',
            'product_types.min'           => 'Debe elegir al menos un tipo de producto.',
        ];
    }

    /**
     * Muestra un listado de los sub proyectos registrados
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $SubprojectsList = ProjectTrackingSubProject::with('Responsable', 'Project')->get()->all();
        foreach ($SubprojectsList as $subproject) {
            $subproject['responsable_name'] = $subproject->Responsable->name;
            $subproject['project_name'] = $subproject->Project->name;
        }
        /* Condicional que oculta el registro común si existe el módulo de Talento Humano */
        if (Module::has('Payroll')) {
            $payroll = true;
        } else {
            $payroll = false;
        }
        return response()->json(['records' => $SubprojectsList, 'payroll' => $payroll], 200);
    }

    /**
     * Obtiene los sub proyectos registrados
     *
     * @return JsonResponse
     */
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
                'product_type_ids' => $subproject->getProductTypeIds(),
                // 'product_type_id' => $subproject->project->type_product_id,
                'responsable_id' => $subproject->responsable,
                'start_date' => $subproject->start_date,
                'end_date' => $subproject->end_date
            ]);
        }
        return response()->json($subprojects);
    }

    /**
     * Muestra el formulario para un nuevo registro de subproyectos
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Guarda un nuevo registro de subproyectos
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validateRules = array_replace($this->validateRules, [
            'start_date' => [
                'required',
                'before_or_equal:end_date',
                function ($attribute, $value, $fail) use ($request) {
                    $project = ProjectTrackingProject::find($request->project_id);
                    if ($value < $project->start_date || $value > $project->end_date) {
                        $fail('La fecha de inicio del subproyecto debe estar entre la fecha de inicio y la fecha de culminación del proyecto asociado.');
                    }
                },
            ],
            'end_date' => [
                'required',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    $project = ProjectTrackingProject::find($request->project_id);
                    if ($value < $project->start_date || $value > $project->end_date) {
                        $fail('La fecha Fin del subproyecto debe estar entre la fecha de inicio y la fecha de culminación del proyecto asociado.');
                    }
                },
            ],
        ]);
        $this->validate($request, $validateRules, $this->messages);

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
        foreach ($request->product_types as $product_type) {
            $projecttrackingSubProject->productTypes()->attach($product_type['id']);
        }
        return response()->json(['record' => $projecttrackingSubProject, 'message' => 'Success'], 200);
    }

    /**
     * Retorna un objeto con la informacion del proyecto correspondiente al id junto a sus tipos de productos asociados
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    JsonResponse
     */
    public function show($id): JsonResponse
    {
        $subProject = ProjectTrackingSubProject::query()
            ->where('id', $id)
            ->with([
                'Responsable',
                'Project',
                'productTypes',
            ])->first();
        $selectedProductTypes = [];
        array_push($selectedProductTypes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($subProject->productTypes as $productType) {
            array_push($selectedProductTypes, [
                'id' => $productType->id,
                'text' => $productType->name
            ]);
        }
        return response()->json([
            'record' => $subProject,
            'selected_product_types' => $selectedProductTypes,
        ], 200);
    }

    /**
     * Muestra el formulario de edición de un subproyecto
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
     * Actualiza los datos de un subproyecto
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $projecttrackingSubProject = ProjectTrackingSubProject::find($request->input('id'));
        $validateRules = array_replace($this->validateRules, [
            'name'        => [
                'required',
                Rule::unique('project_tracking_sub_projects')->ignore($projecttrackingSubProject->id),
                'max:100',
            ],
        ]);
        $this->validate($request, $validateRules, $this->messages);

        $productTypeIds = [];
        $projecttrackingSubProject->project_id  = $request->project_id;
        $projecttrackingSubProject->name  = $request->name;
        $projecttrackingSubProject->description = $request->description;
        $projecttrackingSubProject->code  = $request->code;
        $projecttrackingSubProject->responsable_id  = $request->responsable_id;
        $projecttrackingSubProject->start_date  = $request->start_date;
        $projecttrackingSubProject->end_date  = $request->end_date;
        $projecttrackingSubProject->financement_amount  = $request->financement_amount;
        $projecttrackingSubProject->currency_id = $request->currency_id;
        foreach ($request->product_types as $product_type) {
            $productTypeIds[] = $product_type['id'];
        }
        $projecttrackingSubProject->productTypes()->sync($productTypeIds);
        $projecttrackingSubProject->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un subproyecto
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $projecttrackingSubProject = ProjectTrackingSubProject::find($id);
        $projecttrackingSubProject->delete();
        return response()->json(['record' => $projecttrackingSubProject, 'message' => 'Success'], 200);
    }

    /**
     * Listado de subproyectos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectTrackingSubProject(): JsonResponse
    {
        return response()->json(template_choices('Modules\ProjectTracking\Models\ProjectTrackingSubProject', 'name', '', true));
    }

    /**
     * Obtiene los detalles de un subproyecto
     *
     * @param integer $id Identificador del subproyecto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailSubProject($id): JsonResponse
    {
        $subProject = ProjectTrackingSubProject::with([
            'Responsable',
            'Project',
        ])->find($id);
        $selectedProductTypes = [];
        foreach ($subProject->productTypes as $productType) {
            array_push($selectedProductTypes, [
                'id' => $productType->id,
                'text' => $productType->name
            ]);
        }
        return response()->json([
            'result' => true,
            'records' => $subProject,
            'selected_product_types' => $selectedProductTypes,
        ], 200);
    }
}
