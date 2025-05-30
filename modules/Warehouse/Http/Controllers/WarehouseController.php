<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Warehouse\Models\WarehouseInstitutionWarehouse;
use Modules\Warehouse\Models\WarehouseInventoryProduct;
use Modules\Warehouse\Models\Warehouse;
use App\Models\Institution;
use App\Models\Parameter;

/**
 * @class WarehouseController
 * @brief Controlador de los Almacenes
 *
 * Clase que gestiona los almacenes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseController extends Controller
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
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:warehouse.setting.warehouse');
        $this->middleware('permission:warehouse.setting.warehouse.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:warehouse.setting.warehouse.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:warehouse.setting.warehouse.delete', ['only' => ['destroy']]);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'      => ['required', 'max:100'],
            'address'   => ['required'],
            'country_id' => ['required'],
            'estate_id' => ['required'],
            'municipality_id' => ['required'],
            'parish_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'      => 'El campo nombre del almacén es obligatorio.',
            'name.max'           => 'El campo nombre del almacén no debe ser mayor que 100 caracteres.',
            'country_id.required'         => 'El campo país es obligatorio.',
            'estate_id.required'          => 'El campo estado es obligatorio.',
            'municipality_id.required'       => 'El campo municipio es obligatorio.',
            'address.required'   => 'El campo dirección es obligatorio.',
            'parish_id.required' => 'El campo parroquia es obligatorio.'
        ];
    }

    /**
     * Muestra un listado de los almacenes registrados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $institution  Identificador único de la institución que gestiona el almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($institution = null)
    {
        if (!is_null($institution)) {
            return response()->json(['records' => WarehouseInstitutionWarehouse::where('institution_id', $institution)
                ->with(
                    ['warehouse' =>
                    function ($query) {
                        $query->with(['parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country');
                                }]);
                            }]);
                        }]);
                    },'institution']
                )->get()], 200);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
            $institution = $institution->id;
            return response()->json(['records' => WarehouseInstitutionWarehouse::where('institution_id', $institution)
                ->with(
                    ['warehouse' =>
                    function ($query) {
                        $query->with(['parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country');
                                }]);
                            }]);
                        }]);
                    },'institution']
                )->get()], 200);
        }
    }

    /**
     * Valida y Registra un nuevo almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $warehouse = Warehouse::create([
            'name'      => $request->input('name'),
            'address'   => $request->input('address'),
            'parish_id' => $request->input('parish_id'),
            'active'    => !empty($request->active) ? $request->input('active') : false,
        ]);
        if (empty($request->institution_id)) {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $institution_id = empty($request->institution_id) ? $institution->id : $request->institution_id;

        $warehouse_institution = WarehouseInstitutionWarehouse::create([
            'institution_id' => $institution_id,
            'warehouse_id'   => $warehouse->id,
            'main'           => !empty($request->main) ? $request->input('main') : false,
        ]);

        $paramMultiWarehouse = Parameter::where([
            'active' => true, 'required_by' => 'warehouse',
            'p_key' => 'multi_warehouse', 'p_value' => 'true'
        ])->first();

        if (is_null($paramMultiWarehouse) || ($paramMultiWarehouse->p_value == false)) {
            $inst_wares = WarehouseInstitutionWarehouse::where('institution_id', $institution_id)
                ->with('warehouse')->get();

            foreach ($inst_wares as $inst_ware) {
                if ($inst_ware->warehouse_id != $warehouse->id) {
                    $record = Warehouse::find($inst_ware->warehouse_id);
                    $record->active = ($warehouse->active == true) ? false : $record->active;
                    $record->save();

                    if (!empty($request->main) && ($inst_ware->main == $request->main)) {
                        $inst_ware->main = !$inst_ware->main;
                        $inst_ware->save();
                    }
                }
            }
        }

        return response()->json(['record' => $warehouse, 'message' => 'Success'], 200);
    }


    /**
     * Actualiza la información de los almacenes registrados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     * @param  \Modules\Warehouse\Models\Warehouse  $warehouse Datos del almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $warehouse->name      = $request->input('name');
        $warehouse->address   = $request->input('address');
        $warehouse->parish_id = $request->input('parish_id');
        $warehouse->active    = !empty($request->active) ? $request->input('active') : false;
        $warehouse->save();

        if (empty($request->institution_id)) {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $institution_id =  empty($request->institution_id) ? $institution->id : $request->institution_id;

        $warehouse_institution = WarehouseInstitutionWarehouse::where('institution_id', $institution_id)
                                ->where('warehouse_id', $warehouse->id)->first();
        $paramMultiWarehouse = Parameter::where([
            'active' => true, 'required_by' => 'warehouse',
            'p_key' => 'multi_warehouse', 'p_value' => 'true'
        ])->first();

        if (is_null($paramMultiWarehouse) || ($paramMultiWarehouse->p_value == false)) {
            $inst_wares = WarehouseInstitutionWarehouse::where('institution_id', $institution_id)
                ->with('warehouse')->get();

            foreach ($inst_wares as $inst_ware) {
                if ($inst_ware->warehouse_id != $warehouse->id) {
                    $record = Warehouse::find($inst_ware->warehouse_id);
                    $record->active = ($warehouse->active == true) ? false : $record->active;
                    $record->save();

                    if (!empty($request->main) && ($inst_ware->main == $request->main)) {
                        $inst_ware->main = !$inst_ware->main;
                        $inst_ware->save();
                    }
                }
            }
        }

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer $id Identificador único del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $inst_ware = WarehouseInstitutionWarehouse::find($id);
        $warehouse = Warehouse::find($inst_ware->warehouse_id);
        $inventoryProduct = WarehouseInventoryProduct::where('warehouse_institution_warehouse_id', $inst_ware->id);
        if (count($inventoryProduct->get()) > 0) {
            return response()->json([
                'error'   => true,
                'message' => 'El almacén no se puede eliminar, debido a que ya posee un histórico en inventario.'
            ], 200);
        } else {
            $inst_ware->delete();
            $warehouse->delete();
            return response()->json(['record' => $warehouse, 'message' => 'Success'], 200);
        }
    }

    /**
     * Gestiona un almacén
     *
     * @param integer $id Identificador del almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manage($id)
    {
        $warehouse_inst = WarehouseInstitutionWarehouse::where('warehouse_id', $id)->first();
        $warehouse_inst->manage = !$warehouse_inst->manage;
        $warehouse_inst->save();

        return response()->json(
            [
                'records' => WarehouseInstitutionWarehouse::where('institution_id', $warehouse_inst->institution_id)
                ->with(
                    ['warehouse' =>
                    function ($query) {
                        $query->with(['parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country');
                                }]);
                            }]);
                        }]);
                    },'institution']
                )->get(),
                'manage' => $warehouse_inst->manage],
            200
        );
    }

    /**
     * Construye un arreglo de elementos para usar en plantillas blade
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $institution   Institucion que gestiona los almacenes a ser buscados
     *
     * @return array
     */

    public function getWarehouses($institution = null)
    {
        /*
         |  Si no hay datos sobre la institución de gestión se retornan los almacenes
         |  de la institucion por defecto y activa según la configuración del sistema
         */
        if (is_null($institution)) {
            $institution = Institution::where('active', true)->where('default', true)->first();
            $institution = $institution->id;
        }

        $records = WarehouseInstitutionWarehouse::where('institution_id', $institution)
        ->with(['warehouse' => function ($query) {
            $query->where('active', true)->get();
        }])->get();

        /* Inicia la opción vacia por defecto */
        $options = (count($records) >= 1) ? [['id' => '', 'text' => 'Seleccione...']] : [];

        foreach ($records as $rec) {
            if ($rec->warehouse) {
                $text = $rec->warehouse->name;
                array_push($options, ['id' => $rec->id, 'text' => $text]);
            }
        }
        return $options;
    }
}
