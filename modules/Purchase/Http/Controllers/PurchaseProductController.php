<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Purchase\Models\PurchaseProduct;
use Modules\Purchase\Exports\PurchaseProductExport;
use Modules\Purchase\Imports\PurchaseProductImport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class PurchaseProductController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseProductController extends Controller
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
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required'],
            'code' => ['required', 'max:300', 'unique:purchase_products,code'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre del insumo o producto es obligatorio.',
            'name.max' => 'El campo nombre del insumo o producto no debe ser mayor que 300 caracteres.',
            'code.required' => 'El campo código es obligatorio.',
            'code.max' => 'El campo código no debe ser mayor que 300 caracteres.',
            'code.unique' => 'El campo código ya ha sido registrado.',
        ];
    }

    /**
     * Obtiene la lista de productos
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseProduct::orderBy('id')->get()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo producto
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo producto
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        PurchaseProduct::create([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return response()->json(['records' => PurchaseProduct::orderBy('id')->get(), 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un producto
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar un nuevo producto
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza los datos de un producto
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $purchaseProduct = PurchaseProduct::find($id);
        $this->validateRules['code'] = [
            'code' => ['required', 'max:300', 'unique:purchase_products,code,' . $purchaseProduct->id],
        ];

        $this->validate($request, $this->validateRules, $this->messages);

        $purchaseProduct->name = $request->name;
        $purchaseProduct->code = $request->code;
        $purchaseProduct->save();

        return response()->json(['records' => PurchaseProduct::orderBy('id')->get(), 'message' => 'Success'], 200);
    }

    /**
     * Elimina un producto
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $purchaseProduct = PurchaseProduct::find($id);
        $purchaseProduct->delete();

        return response()->json(['records' => PurchaseProduct::orderBy('id')->get(), 'message' => 'Success'], 200);
    }

    /**
     * Realiza la acción necesaria para exportar los datos del modelo PurchaseProduct
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return BinaryFileResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function export()
    {
        return Excel::download(new PurchaseProductExport(), 'purchase-products.xlsx');
    }

    /**
     * Realiza la acción necesaria para importar los datos suministrados en un archivo para el modelo PurchaseProduct
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Objeto que permite cargar el archivo con la información a ser importada
     */
    public function import(Request $request)
    {
        Excel::import(new PurchaseProductImport(), request()->file('file'));
        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene los datos de los productos registrados
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getProducts(Request $request)
    {
        $data = [];
        $query = $request->input('query');

        if (!$query) {
            return response()->json($data);
        }

        $products = PurchaseProduct::where('name', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->get();

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->id,
                'text' => $product->code . ' - ' . $product->name,
                'code' => $product->code,
            ];
        }

        return response()->json($data);
    }
}
