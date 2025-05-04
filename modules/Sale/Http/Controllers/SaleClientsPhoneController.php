<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleClientsPhone;

/**
 * Eliminar
 */
class SaleClientsPhoneController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        // $this->middleware('permission:sale.setting.phone');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    public function client($id)
    {
        return response()->json(['records' => SaleClientsPhone::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'phone' => ['required', 'max:100'],
            'sale_client_id' => ['required'],
        ]);
        $id = $request->input('sale_client_id');

        $phone = SaleClientsPhone::create([
            'phone' => $request->input('phone'),
            'sale_client_id' => $request->input('sale_client_id'),
        ]);

        return response()->json(['records' => SaleClientsPhone::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function update(Request $request, SaleClientsPhone $phone)
    {
        $this->validate($request, [
            'phone' => ['required', 'max:100'],
            'sale_client_id' => ['required'],
        ]);

        $phone->phone = $request->input('phone');
        $phone->sale_client_id = $request->input('sale_client_id');
        $phone->save();

        $id = $phone->sale_client_id;

        return response()->json(['records' => SaleClientsPhone::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @return JsonResponse
     */
    public function destroy(SaleClientsPhone $phone)
    {
        $phone->delete();
        return response()->json(['record' => $phone, 'message' => 'Success'], 200);
    }
}
