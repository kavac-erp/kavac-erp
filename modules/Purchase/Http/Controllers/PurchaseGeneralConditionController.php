<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class PurchaseGeneralConditionController
 * @brief Gestiona la información de las condiciones generales de ccompra o servicios
 *
 * Clase que gestiona la información de las condiciones generales de ccompra o servicios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseGeneralConditionController extends Controller
{
    use ValidatesRequests;

    /**
     * Obtiene información de las condiciones generales de compras o servicios
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse    Objeto con la colección de las condiciones generales de compras o servicios
     */
    public function index()
    {
        $purchaseCondition = Parameter::where([
            'p_key' => 'purchase_general_condition',
            'required_by' => 'purchase',
            'active' => true
        ])->first();
        $serviceCondition = Parameter::where([
            'p_key' => 'service_general_condition',
            'required_by' => 'purchase',
            'active' => true
        ])->first();
        $records = [
            'purchase' => $purchaseCondition,
            'service' => $serviceCondition
        ];
        return response()->json(['records' => $records], 200);
    }

    /**
     * Almacena una condición general de compra o servicio
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'purchase_general_conditions' => ['required'],
            'service_general_conditions' => ['required']
        ], [
            'purchase_general_conditions.required' => 'La condición general de compra es obligatoria',
            'service_general_conditions.required' => 'La condición general de servicio es obligatoria'
        ]);

        DB::transaction(function () use ($request) {
            // Condiciones generales de compras
            Parameter::updateOrCreate([
                'p_key' => 'purchase_general_condition'
            ], [
                'p_value' => $request->purchase_general_conditions,
                'required_by' => 'purchase',
                'active' => true
            ]);
            // Condiciones generales de servicios
            Parameter::updateOrCreate([
                'p_key' => 'service_general_condition'
            ], [
                'p_value' => $request->service_general_conditions,
                'required_by' => 'purchase',
                'active' => true
            ]);
        });

        return response()->json([
            'result' => true
        ], 200);
    }
}
