<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleWarehouseInstitutionWarehouse;
use Modules\Sale\Models\SaleWarehouse;

class SaleWarehouseController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:sale.setting.warehouse', ['only' => 'index']);
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
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
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        //return view('sale::create');
    }

    /**
     * Realiza la validación de un almacen
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
                   //'name' => ['required', 'unique:sale_warehouses,name', 'regex:/([A-Za-z\s])\w+/u','max:200'],
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
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->saleWarehouseValidate($request);

        $this->validate($request, [
            'name' => ['required', 'unique:sale_warehouses,name', 'regex:/([A-Za-z\s])\w+/u','max:200']
        ]);

        //Define almacén principal
        /*
        if ($request->input('main') == true) {
            $main = SaleWarehouse::where('main', '=', true)->update(['main' => false]);
        }*/
        //Guarda datos de almacen.
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
     * Show the specified resource.
     * @return Renderable
     */
    public function show()
    {
        //return view('sale::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     */
    public function edit()
    {
        //return view('sale::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Renderable
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
     * Remove the specified resource from storage.
     * @return JsonResponse
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
    * @return \Illuminate\Http\JsonResponse    Json con los datos de los alamacenes registrados
    */
    public function getSaleWarehouseMethod()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleWarehouse', 'name', '', true));
    }
}
