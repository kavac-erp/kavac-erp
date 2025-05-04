<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProductController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProductController extends Controller
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
        /** @var object Contiene los registros del personal, de proyecto, de subproyecto, de los tipos de producto y de las dependencias */
        $ProductsList = ProjectTrackingProduct::with(['Project', 'SubProject', 'Responsable', 'TypeProduct', 'Dependency'])->get();
        foreach ($ProductsList as $product) {
            $product['responsable_name'] = $product->Responsable->name;
            $product['project_name'] = $product->Project ? $product->Project->name : '';
            $product['subproject_name'] = $product->SubProject ? $product->SubProject->name : '';
            $product['type_product_name'] = $product->TypeProduct->name;
            $product['dependency_name'] = $product->Dependency->name;
        }
        /** @var condition Condicional que oculta el registro común si existe el módulo de Talento Humano */
        if (Module::has('Payroll')) {
            $payroll = true;
        } else {
            $payroll = false;
        }
        return response()->json(['records' => $ProductsList, 'payroll' => $payroll], 200);
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

    public function getProducts()
    {
        $productsList = ProjectTrackingProduct::all();
        $products = [];
        array_push($products, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($productsList->all() as $product) {
            array_push($products, [
                'id' => $product->id,
                'text' => $product->name,
                'name' => $product->name,
                'responsable_id' => $product->responsable,
                'dependency_id' => $product->dependency,
                'start_date' => $product->start_date,
                'end_date' => $product->end_date
            ]);
        }
        return response()->json($products);
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
                'project_id' => ['nullable'],
                'subproject_id' => ['nullable'],
                'code' => ['nullable', 'max:100'],
                'dependency_id' => ['required'],
                'responsable_id' => ['required'],
                'type_product_id' => ['required'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
            ],
            [],
            [
                'project_id' => 'Proyecto Asociado',
                'subproject_id' => 'Subproyecto Asociado',
                'code' => 'Código',
                'dependency_id' => 'Dependencia',
                'responsable_id' => 'Responsable del producto',
                'type_product_id' => 'Tipo de producto',
                'start_date' => 'Fecha de inicio',
                'end_date' => 'Fecha de culminación',
            ]
        );

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
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
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
        return response()->json(['records' => ProjectTrackingProduct::with(['Responsable', 'Project', 'Subproject', 'TypeProduct', 'Dependency'])->find($id)]);
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
                'project_id' => ['nullable'],
                'subproject_id' => ['nullable'],
                'code' => ['nullable', 'max:100'],
                'dependency_id' => ['required'],
                'responsable_id' => ['required'],
                'type_product_id' => ['required'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
            ],
            [],
            [
                'project_id' => 'Proyecto Asociado',
                'subproject_id' => 'Subproyecto Asociado',
                'code' => 'Código',
                'dependency_id' => 'Dependencia',
                'responsable_id' => 'Responsable del producto',
                'type_product_id' => 'Tipo de producto',
                'start_date' => 'Fecha de inicio',
                'end_date' => 'Fecha de culminación',
            ]
        );
        $product = ProjectTrackingProduct::find($request->input('id'));
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
        $product->save();

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
        $product = ProjectTrackingProduct::find($id);
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }
}
