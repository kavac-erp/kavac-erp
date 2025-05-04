<?php

/**
 * Descripcion general
 */

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Warehouse\Models\WarehouseProduct;
use Modules\Warehouse\Models\WarehouseProductValue;
use Modules\Warehouse\Exports\WarehouseProductExport;
use Modules\Warehouse\Imports\WarehouseProductImport;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\WarehouseProductAttribute;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Warehouse\Models\WarehouseInstitutionWarehouse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class WarehouseProductController
 * @brief Controlador de los productos de almacén
 *
 * Clase que gestiona los productos almacenables
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseProductController extends Controller
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
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:warehouse.setting.product');
        $this->middleware('permission:warehouse.setting.product.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:warehouse.setting.product.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.setting.product.delete', ['only' => ['destroy']]);
        $this->middleware('permission:warehouse.setting.product.import', ['only' => ['export']]);
        $this->middleware('permission:warehouse.setting.product.export', ['only' => ['import']]);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'max:100', 'unique:warehouse_products,name', 'titlecase'],
            'description' => ['required'],
            'measurement_unit_id' => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre del insumo es obligatorio.',
            'name.max' => 'El campo nombre del insumo no debe ser mayor que 100 caracteres.',
            'name.unique:warehouse_products,name' => 'El campo nombre ya ha sido registrado anteriormente',
            'name.titlecase' => 'El campo nombre debe ser escrito en mayusculas',
            'description.required' => 'El campo descripción es obligatorio.',
            'measurement_unit_id.required' => 'El campo unidad de medida es obligatorio.'
        ];
    }

    /**
     * Muestra un listado de los productos almacenables registrados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => WarehouseProduct::with('warehouseProductAttributes')->get()], 200);
    }

    /**
     * Valida y Registra un nuevo producto almacenable
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $product = WarehouseProduct::create(
            [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'define_attributes' => !empty($request->define_attributes)
            ? $request->input('define_attributes')
            : false,
            'accounting_account_id' => $request->accounting_account_id,
            'measurement_unit_id' => $request->input('measurement_unit_id'),
            'history_tax_id' => $request->input('history_tax_id'),
            ]
        );

        if ($product->define_attributes) {
            foreach ($request->warehouse_product_attributes as $att) {
                $attribute = WarehouseProductAttribute::create(
                    [
                        'name' => $att['name'],
                        'warehouse_product_id' => $product->id
                    ]
                );
            }
        }

        /**
         * Sanitizar el nombre del producto
         * (Sin espacios extras, solo mayusculas en el comienzo de cada palabra)
         */
        // $clean_name = preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $product->name);
        // $product->name = ucwords($clean_name);

        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de los Productos Almacenables
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request                   $request Datos de la petición
     * @param \Modules\Warehouse\Models\WarehouseProduct $product Registro a ser actualizado
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WarehouseProduct $product)
    {
        $this->messages = [
            'name.required' => 'El campo nombre del insumo es obligatorio.',
            'name.max' => 'El campo nombre del insumo no debe ser mayor que 100 caracteres.',
            'name.titlecase' => 'El campo nombre debe ser escrito en mayusculas',
            'description.required' => 'El campo descripción es obligatorio.',
            'measurement_unit_id.required' => 'El campo unidad de medida es obligatorio.'
        ];

        $this->validate(
            $request,
            [
            'name' => ['required', 'max:100', 'titlecase'],
            'description' => ['required'],
            'measurement_unit_id' => ['required']
            ],
            $this->messages
        );

        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->define_attributes = !empty($request->define_attributes)
            ? $request->input('define_attributes')
            : false;
        $product->accounting_account_id = $request->accounting_account_id;
        $product->measurement_unit_id = $request->input('measurement_unit_id');
        $product->history_tax_id = $request->input('history_tax_id');
        $product->save();

        $product_attributes = WarehouseProductAttribute::where('warehouse_product_id', $product->id)->get();

        /* Busco si en la solicitud se eliminaron atributos registrados anteriormente */
        foreach ($product_attributes as $product_att) {
            $equal = false;
            foreach ($request->warehouse_product_attributes as $att) {
                if ($att["name"] == $product_att->name) {
                    $equal = true;
                    break;
                }
            }
            if ($equal == false) {
                $value = $product_att->warehouseProductValue();
                if ($value) {
                    $value->delete();
                }
                $product_att->delete();
            }
        }

        /* Registro los nuevos atributos */
        if ($product->define_attributes == true) {
            foreach ($request->warehouse_product_attributes as $att) {
                $attribute = WarehouseProductAttribute::where('name', $att['name'])
                    ->where('warehouse_product_id', $product->id)->first();
                if (is_null($attribute)) {
                    $attribute = WarehouseProductAttribute::create(
                        [
                        'name' => $att['name'],
                        'warehouse_product_id' => $product->id
                        ]
                    );
                }
            }
        }

        /**
         * Sanitizar el nombre del producto
         * (Sin espacios extras, solo mayusculas en el comienzo de cada palabra)
         */
        // $clean_name = preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $product->name);
        // $product->name = ucwords($clean_name);

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un Producto Almacenable
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param WarehouseProduct $product Modelo del producto de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(WarehouseProduct $product)
    {
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Muestra una lista de los productos almacenables para elementos del tipo select
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return array con los registros a mostrar
     */
    public function getWarehouseProducts()
    {
        return template_choices('Modules\Warehouse\Models\WarehouseProduct', 'name', '', true);
    }

    /**
     * Consulta la informacion de un producto
     *
     * @param integer $product_id Identificador único del producto
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com | jrosas@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductMeasurementUnit($product_id)
    {
        return response()->json(
            ['record' => WarehouseProduct::with('MeasurementUnit')->find($product_id)]
        );
    }

    /**
     * Muestra una lista de los atributos de un producto
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param integer $product_id Identificador único del producto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductAttributes($product_id)
    {
        return response()->json(
            [
            'records' => WarehouseProductAttribute::where(
                'warehouse_product_id',
                $product_id
            )->get()
            ]
        );
    }

    /**
     * Muestra una lista de las unidades de medida de los productos almacenables para elementos del tipo select
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return array con los registros a mostrar
     */
    public function getMeasurementUnits()
    {
        return template_choices('App\Models\MeasurementUnit', ['acronym', '-', 'name'], '', true);
    }

    /**
     * Muestra una lista de los productos registrados para su uso en elementos gráficos del sistema
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param string $type  Tipo de almacenamiento del producto (exist = Existente, reserved = Reservado)
     * @param string $order Orden de filtro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInventoryProducts($type, $order = 'desc')
    {
        $fields = WarehouseInventoryProduct::with(
            [
            'warehouseProduct' => function ($query) {
                $query->with('measurementUnit');
            }
            ],
            'currency'
        );
        if ($type == 'exist') {
            $fields = ($order == 'desc') ? $fields->orderBy('exist', 'desc')->get() :
                $fields->orderBy('exist', 'asc')->get();
        } else {
            $fields = ($order == 'desc') ? $fields->orderBy('reserved', 'desc')->get() :
                $fields->orderBy('reserved', 'asc')->get();
        }
        return response()->json(['records' => $fields], 200);
    }

    /**
     * Realiza la acción necesaria para exportar los datos del modelo WarehouseProduct
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return BinaryFileResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function export()
    {
        return Excel::download(new WarehouseProductExport(), 'warehouse-products.xlsx');
    }

    /**
     * Realiza la acción necesaria para importar los datos suministrados en un archivo para el modelo WarehouseProduct
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function import(Request $request)
    {
        Excel::import(new WarehouseProductImport(), request()->file('file'));
        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra una lista de los atributos de un producto
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRules(Request $request)
    {
        $rules = [];
        $product_id = $request['warehouse_inventory_product']['warehouse_product_id'];
        $currency = $request['warehouse_inventory_product']['currency_id'];
        $quantity = $request['warehouse_inventory_product']['quantity'];
        $value = $request['warehouse_inventory_product']['unit_value'];

        $inst_ware = WarehouseInstitutionWarehouse::where('warehouse_id', $request->warehouse_id)
            ->where('institution_id', $request->institution_id)->first();

        /* Se busca en el inventario por producto y unidad si existe un registro previo */

        $inventory = WarehouseInventoryProduct::with('warehouseInventoryRule')
            ->where('warehouse_product_id', $product_id)
            ->where('warehouse_institution_warehouse_id', $inst_ware->id)
            ->where('unit_value', $value)->get();

        /* Si existe un registro previo se verifican los atributos del nuevo ingreso */
        if (count($inventory) > 0) {
            foreach ($inventory as $product_inventory) {
                /* Ciclo de inventario. Define si los atributos coinciden con los registrados */
                $equal = true;

                foreach ($request['warehouse_inventory_product']['warehouse_product_attributes'] as $attribute) {
                    $name = $attribute['name'];
                    $val = $attribute['value'];

                    $product_att = WarehouseProductAttribute::where('warehouse_product_id', $product_id)
                        ->where('name', $name)->first();

                    if (!is_null($product_att)) {
                        $product_value = WarehouseProductValue::where('value', $val)
                            ->where('warehouse_product_attribute_id', $product_att->id)
                            ->where('warehouse_inventory_product_id', $product_inventory->id)->first();

                        if (is_null($product_value)) {
                            /* Si el valor de este atributo no existe, son diferentes */
                            $equal = false;
                            break;
                        }
                    } else {
                        $equal = false;
                        break;
                    }
                }
                if ($equal === true) {
                    /* Si se encuentra el producto en inventario, se develven sus reglas de abastecimiento */
                    $rules = $product_inventory->warehouseInventoryRule;
                }
            }
        }
        return response()->json(['records' => $rules], 200);
    }
}
