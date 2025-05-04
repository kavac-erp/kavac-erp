<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sale\Models\Institution;
use Modules\Sale\Models\SaleWarehouse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleWarehouseInstitutionWarehouse;

/**
 * @class SaleWarehouseController
 * @brief Gestiona los datos de los almacenes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleWarehouseController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.setting.warehouse', ['only' => 'index']);
    }

    /**
     * Muestra el listado de almacenes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SaleWarehouse::all()], 200);

        if (!is_null($institution)) {
            return response()->json(['records' => SaleWarehouseInstitutionWarehouse::where('institution_id', $institution)
                ->with(
                    ['sale_warehouse' =>
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
            return response()->json(['records' => SaleWarehouseInstitutionWarehouse::where('institution_id', $institution)
                ->with(
                    ['sale_warehouse' =>
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
     * Muestra el formulario de creación de un almacén
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Realiza la validación de un almacen
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return void
     */
    public function saleWarehouseValidate(Request $request)
    {
        $attributes = [
            'name' => 'Institución que gestiona el Almacén',
            'institution_id' => 'Nombre de Almacén',
            'country_id' => 'Ciudad',
            'estate_id' => 'Estado',
            'municipality_id' => 'Municipio',
            'parish_id' => 'País',
            'address' => 'Dirección'
        ];

        $validation = [];
        $validation['name'] = ['required', 'regex:/([A-Za-z\s])\w+/u','max:200'];
        $validation['institution_id'] = ['required'];
        $validation['country_id'] = ['required'];
        $validation['estate_id'] = ['required'];
        $validation['municipality_id'] = ['required'];
        $validation['parish_id'] = ['required'];
        $validation['address'] = ['required'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Almacena los datos de un almacen
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->saleWarehouseValidate($request);

        $this->validate($request, [
            'name' => ['required', 'unique:sale_warehouses,name', 'regex:/([A-Za-z\s])\w+/u','max:200']
        ]);

        //Guarda datos de almacen.
        $institution = Institution::where('active', true)->where('default', true)->first();
        $institution_id = empty($request->institution_id) ? $institution->id : $request->institution_id;

        $SaleWarehouse = SaleWarehouse::create([
            'name' => $request->name,
            'institution_id' => $institution_id,
            'address' => $request->address,
            'country_id' => $request->country_id,
            'estate_id' => $request->estate_id,
            'parish_id' => $request->parish_id,
            'municipality_id' => $request->municipality_id,
            'active' => !empty($request->input('active')) ? $request->input('active') : false,
            'main' => !empty($request->input('main')) ? $request->input('main') : false
        ]);


        if (empty($request->institution_id)) {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $sale_warehouse_institution = SaleWarehouseInstitutionWarehouse::create([
            'institution_id' => $institution_id,
            'sale_warehouse_id'   => $SaleWarehouse->id,
            'main' => !empty($request->main) ? $request->input('main') : false,
        ]);

        return response()->json(['record' => $SaleWarehouse, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un almacen
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario de edición de un almacen
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza la información de un almacen
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $saleWarehouse = SaleWarehouse::find($id);

        $this->saleWarehouseValidate($request);
        $this->validate($request, [
            'name' => ['required', 'unique:sale_warehouses,name,' . $saleWarehouse->id, 'regex:/([A-Za-z\s])\w+/u','max:200']
        ]);

        $saleWarehouse->name = $request->name;
        $saleWarehouse->institution_id = $request->institution_id;
        $saleWarehouse->address = $request->address;
        $saleWarehouse->country_id = $request->country_id;
        $saleWarehouse->estate_id = $request->estate_id;
        $saleWarehouse->parish_id = $request->parish_id;
        $saleWarehouse->municipality_id = $request->municipality_id;
        $saleWarehouse->active = !empty($request->input('active')) ? $request->input('active') : false;
        $saleWarehouse->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina los datos de un almacen
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $sale_warehouse_institution = SaleWarehouseInstitutionWarehouse::where('sale_warehouse_id', $id);
        $saleWarehouse = SaleWarehouse::find($id);
        $sale_warehouse_institution->delete();
        $saleWarehouse->delete();
        return response()->json(['record' => $saleWarehouse, 'message' => 'Success'], 200);
    }

    /**
    * Obtiene los alamacenes registrados
    *
    * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>

    * @return \Illuminate\Http\JsonResponse
    */
    public function getSaleWarehouseMethod()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleWarehouse', 'name', '', true));
    }
}
