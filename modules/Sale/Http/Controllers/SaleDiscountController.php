<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleDiscount;

class SaleDiscountController extends Controller
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
        $this->middleware('permission:sale.setting.discount', ['only' => 'index']);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return response()->json(['records' => SaleDiscount::all()], 200);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
    //    return view('sale::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->saleDiscountValidate($request);

        $SaleDiscount = SaleDiscount::create(['name' => $request->name,'description' => $request->description, 'percent' => $request->percent]);
        return response()->json(['record' => $SaleDiscount, 'message' => 'Success'], 200);
    }

   /**
    * Validacion de los datos
    *
    * @method    saleDiscountValidate
    * @author Ing. Jose Puentes <jpuentes@cenditel.gob.ve>
    * @param     object    Request    $request
    */
    public function saleDiscountValidate(Request $request)
    {
        $attributes = [
        'name' => 'Nombre del descuento',
        'description' => 'Descripción del descuento',
        'percent' => 'Porcentaje del descuento'
        ];
        $validation = [];
        $validation['name'] = ['required', 'max:100', 'unique:sale_discounts,name', 'regex:/([A-Za-z\s])\w+/u'];
        $validation['description'] = ['required', 'max:200'];
        $validation['percent'] = ['required', 'max:3'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
     //   return view('sale::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
     //   return view('sale::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $SaleDiscount = SaleDiscount::find($id);

        $this->saleDiscountValidate($request);

        $SaleDiscount->name  = $request->name;
        $SaleDiscount->description = $request->description;
        $SaleDiscount->percent  = $request->percent;
        $SaleDiscount->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $SaleDiscount = SaleDiscount::find($id);
        $SaleDiscount->delete();
        return response()->json(['record' => $SaleDiscount, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los descuento registrados
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los descuentos
     */
    public function getSaleDiscount()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleDiscount', 'percent', '', true));
    }
}
