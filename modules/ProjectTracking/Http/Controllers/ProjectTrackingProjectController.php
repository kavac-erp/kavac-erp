<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProjectController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProjectController extends Controller
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
     * Define la configuración de la clase
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        // $this->middleware('permission:asset.setting.building');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'unique:Modules\ProjectTracking\Models\ProjectTrackingProject,name', 'max:200'],
            'description' => ['nullable', 'max:200'],
            'project_type_id' => ['required'],
            'code' => ['nullable', 'max:100'],
            'dependency_id' => ['required'],
            'responsable_id' => ['required'],
            'product_types' => ['required', 'array', 'min:1'],
            // 'type_product_id' => ['required'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
            'financing_amount' => ['nullable'],
            'currency_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre es obligatorio',
            'name.unique' => 'El campo nombre debe ser único',
            'name.max' => 'El campo nombre no debe contener mas de 200 caracteres',
            'description.required' => 'El campo descripción es obligatorio.',
            'description.max' => 'El campo descripción no debe contener mas de 200 caracteres',
            'product_types.required' => 'El campo tipos de producto es obligatorio.',
            'product_types.min' => 'Debe elegir al menos un tipo de producto.',
            'project_type_id.required' => 'El campo tipo de proyecto es obligatorio.',
            'dependency_id.required' => 'El campo dependencia es obligatorio.',
            // 'type_product_id.required' => 'El campo tipo de producto es obligatorio.',
            'responsable_id.required' => 'El campo responsable es obligatorio.',
            'financing_amount.required' => 'El campo monto de financiamiento es obligatorio.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'start_date.required' => 'El campo fecha de inicio es obligatorio.',
            'start_date.before_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha de culminación.',
            'end_date.required' => 'El campo fecha de culminación es obligatorio.',
            'end_date.after_or_equal' => 'La fecha de culminación no puede ser anterior a la fecha de inicio.',
        ];
    }

    /**
     * Listado de proyectos
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /* Contiene los registros del personal, tipos de proyecto y los tipos de producto */
        $ProjectsList = ProjectTrackingProject::with([
            'Responsable',
            'ProjectType',
            'productTypes',
            'Dependency',
        ])->get()
            ->all();
        foreach ($ProjectsList as $project) {
            $project['responsable_name'] = $project->Responsable->name;
            $project['project_type_name'] = $project->ProjectType->name;
            $project['type_product_name'] = $project->TypeProduct ? $project->TypeProduct->name : '';
            $project['dependency_name'] = $project->Dependency->name;
        }
        /* Condicional que oculta el registro común si existe el módulo de Talento Humano */
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
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getProjects(): JsonResponse
    {
        $projectsList = ProjectTrackingProject::all();
        $projects = [];
        array_push($projects, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        foreach ($projectsList->all() as $project) {
            array_push($projects, [
                'id' => $project->id,
                'text' => $project->name,
                'name' => $project->name,
                'product_type_ids' => $project->getProductTypeIds(),
                // 'product_type_id' => $project->type_product_id,
                'responsable_id' => $project->responsable,
                'dependency_id' => $project->dependency,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
            ]);
        }
        return response()->json($projects, 200);
    }

    /**
     * Muestra el formulario para crear un nuevo proyecto
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena la información de un nuevo proyecto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->validateRules, $this->messages);

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
        foreach ($request->product_types as $product_type) {
            $project->productTypes()->attach($product_type['id']);
        }
        return response()->json(['record' => $project, 'message' => 'Success'], 200);
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
        $project = ProjectTrackingProject::query()
            ->where('id', $id)
            ->with([
                'Responsable',
                'ProjectType',
                'productTypes',
                'Dependency',
            ])->first();
        $selectedProductTypes = [];
        foreach ($project->productTypes as $productType) {
            array_push($selectedProductTypes, [
                'id' => $productType->id,
                'text' => $productType->name
            ]);
        }
        return response()->json([
            'record' => $project,
            'selected_product_types' => $selectedProductTypes,
        ], 200);
    }

    /**
     * Obtiene la información de un proyecto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function recordInfo($id): JsonResponse
    {
        return response()->json(
            [
                'records' => ProjectTrackingProject::with([
                    'Responsable',
                    'ProjectType',
                    // 'TypeProduct',
                    'productTypes',
                    'Dependency'
                ])
                    ->find($id)
            ],
            200
        );
    }

    /**
     * Muestra el formulario de edición de un proyecto
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
     * Actualiza los datos de un proyecto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $productTypesIds = [];
        $projects = ProjectTrackingProject::find($request->input('id'));
        $validateRules = array_merge($this->validateRules, [
            'name' => [
                'required',
                Rule::unique('project_tracking_projects')->ignore($projects->id),
            ],
        ]);
        $this->validate($request, $validateRules, $this->messages);
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
        foreach ($request->product_types as $product_type) {
            $productTypesIds[] = $product_type['id'];
        }
        $projects->productTypes()->sync($productTypesIds);
        $projects->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un proyecto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function destroy($id): JsonResponse
    {
        $project = ProjectTrackingProject::find($id);
        $project->delete();
        return response()->json(['record' => $project, 'message' => 'Success'], 200);
    }
}
