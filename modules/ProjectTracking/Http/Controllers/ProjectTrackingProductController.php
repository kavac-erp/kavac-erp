<?php

namespace Modules\ProjectTracking\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProductController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProductController extends Controller
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
     * @author    Pedro Buitragp <pbuitrago@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'unique:Modules\ProjectTracking\Models\ProjectTrackingProduct,name', 'max:200'],
            'description' => ['nullable', 'max:200'],
            'project_id' => ['nullable'],
            'subproject_id' => ['nullable'],
            'code' => ['nullable', 'max:100'],
            'dependency_id' => ['required'],
            'responsable_id' => ['required'],
            // 'type_product_id' => ['required'],
            'product_types' => ['required', 'array', 'min:1', 'max:1'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El nombre del producto es obligatorio',
            'name.unique' => 'El campo nombre debe ser único',
            'name.max' => 'El nombre del producto no debe ser mayor a 200 caracteres',
            'description.max' => 'La descripción del producto no debe ser mayor a 200 caracteres',
            'dependency_id.required' => 'La dependencia es obligatoria',
            'responsable_id.required' => 'El responsable del proyecto es obligatorio',
            // 'type_product_id.required' => 'El tipo de producto es obligatorio',
            'product_types.required' => 'El campo tipos de producto es obligatorio.',
            'product_types.max' => 'Debe elegir un solo tipo de producto.',
            'start_date.required' => 'El campo Fecha de inicio es obligatorio',
            'start_date.before_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha de culminación',
            'end_date.required' => 'El campo Fecha de culminación es obligatorio',
            'end_date.after_or_equal' => 'La fecha de culminación no puede ser anterior a la fecha de inicio'
        ];
    }

    /**
     * Obtiene todos los productos registrados en el seguimiento de proyectos
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojó <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        /* Contiene los registros del personal, de proyecto, de subproyecto, de los tipos de producto y de las dependencias */
        $ProductsList = ProjectTrackingProduct::with(['Project', 'SubProject', 'Responsable', 'TypeProduct', 'Dependency'])->get();
        foreach ($ProductsList as $product) {
            $product['responsable_name'] = $product->Responsable->name;
            $product['project_name'] = $product->Project ? $product->Project->name : '';
            $product['subproject_name'] = $product->SubProject ? $product->SubProject->name : '';
            $product['type_product_name'] = $product->TypeProduct->name;
            $product['dependency_name'] = $product->Dependency->name;
        }
        /* Condicional que oculta el registro común si existe el módulo de Talento Humano */
        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $payroll = true;
        } else {
            $payroll = false;
        }
        return response()->json(['records' => $ProductsList, 'payroll' => $payroll], 200);
    }

    /**
     * Muestra el formulario para un nuevo registro de producto
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Retorna un json con todas las actividades para ser usado en un componente <select2>
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    JsonResponse    Datos en formato JSON
     */
    public function getProducts(): JsonResponse
    {
        $productsList = ProjectTrackingProduct::all();
        $products = [];
        array_push($products, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        foreach ($productsList->all() as $product) {
            array_push($products, [
                'id' => $product->id,
                'text' => $product->name,
                'name' => $product->name,
                'product_type_ids' => $product->getProductTypeIds(),
                // 'product_type_id' => $product->type_product_id,
                'responsable_id' => $product->responsable,
                'dependency_id' => $product->dependency,
                'start_date' => $product->start_date,
                'end_date' => $product->end_date
            ]);
        }
        return response()->json($products, 200);
    }

    /**
     * Almacena un nuevo registro de producto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojó <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    JsonResponse    Producto creado
     */
    public function store(Request $request): JsonResponse
    {
        $validateRules = array_replace($this->validateRules, [
            'start_date' => [
                'required',
                'before_or_equal:end_date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->subproject_id) {
                        $subProject = ProjectTrackingSubProject::find($request->subproject_id);

                        if ($value < $subProject->start_date || $value > $subProject->end_date) {
                            $fail('La Fecha de inicio del producto debe estar entre la fecha de inicio y la fecha de culminación del subproyecto asociado.');
                        }
                    }
                    if ($request->project_id) {
                        $project = ProjectTrackingProject::find($request->project_id);

                        if ($value < $project->start_date || $value > $project->end_date) {
                            $fail('La fecha de inicio del proyecto debe estar entre la fecha de inicio y la fecha de culminación del proyecto asociado.');
                        }
                    }
                },
            ],
            'end_date' => [
                'required',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->subproject_id) {
                        $subProject = ProjectTrackingSubProject::find($request->subproject_id);

                        if ($value < $subProject->start_date || $value > $subProject->end_date) {
                            $fail('La Fecha de culminación del producto debe estar entre la fecha de inicio y la fecha de culminación del subproyecto asociado.');
                        }
                    }
                    if ($request->project_id) {
                        $project = ProjectTrackingProject::find($request->project_id);

                        if ($value < $project->start_date || $value > $project->end_date) {
                            $fail('La fecha de culminación del proyecto debe estar entre la fecha de inicio y la fecha de culminación del proyecto asociado.');
                        }
                    }
                },
            ],
        ]);
        $this->validate($request, $validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'project_tracking_products')->first();
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
            ProjectTrackingProduct::class,
            $codeSetting->field
        );

        $product = ProjectTrackingProduct::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'project_id' => $request->input('project_id'),
            'subproject_id' => $request->input('subproject_id'),
            'code' => $code,
            'dependency_id' => $request->input('dependency_id'),
            'responsable_id' => $request->input('responsable_id'),
            'type_product_id' => $request->input('type_product_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        foreach ($request->product_types as $product_type) {
            $product->productTypes()->attach($product_type['id']);
        }
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un producto
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $products = ProjectTrackingProduct::with([
            'Responsable',
            'Project',
            'Subproject',
            'productTypes',
            'Dependency',
        ])->find($id);
        $selectedProductTypes = [];
        foreach ($products->productTypes as $productType) {
            $selectedProductTypes[] = [
                'id' => $productType->id,
                'text' => $productType->name,
            ];
        }
        return response()->json([
            'records' => $products,
            'selected_product_types' => $selectedProductTypes,
        ], 200);
    }

    /**
     * Muestra información de un registro
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     * @author Natanael Rojó <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function recordInfo($id): JsonResponse
    {
        $products = ProjectTrackingProduct::with([
            'Responsable',
            'Project',
            'Subproject',
            'productTypes',
            // 'TypeProduct',
            'Dependency',
        ])->find($id);
        $selectedProductTypes = [];
        foreach ($products->productTypes as $productType) {
            $selectedProductTypes[] = [
                'id' => $productType->id,
                'text' => $productType->name,
            ];
        }
        return response()->json([
            'records' => $products,
            'selected_product_types' => $selectedProductTypes,
        ], 200);
    }

    /**
     * Muestra el formulario para editar un registro
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
     * Actualiza la información de un producto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    JsonResponse    Producto actualizado
     */
    public function update(Request $request, $id): JsonResponse
    {
        $productTypesIds = [];
        $product = ProjectTrackingProduct::find($request->input('id'));
        $validateRules = array_replace($this->validateRules, [
            'name' => [
                'required',
                'max:200',
                Rule::unique('project_tracking_products')->ignore($product->id),
            ],
        ]);
        $this->validate($request, $validateRules, $this->messages);
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->project_id = $request->input('project_id');
        $product->subproject_id = $request->input('subproject_id');
        $product->code = $request->input('code');
        $product->dependency_id = $request->input('dependency_id');
        $product->responsable_id = $request->input('responsable_id');
        $product->type_product_id = $request->input('type_product_id');
        $product->start_date = $request->input('start_date');
        $product->end_date = $request->input('end_date');
        foreach ($request->product_types as $product_type) {
            $productTypesIds[] = $product_type['id'];
        }
        $product->productTypes()->sync($productTypesIds);
        $product->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un producto
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    JsonResponse    Producto eliminado
     */
    public function destroy($id): JsonResponse
    {
        $product = ProjectTrackingProduct::find($id);
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }
}
