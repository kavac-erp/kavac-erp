<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Parameter;

/**
 * @class      PurchaseParameterController
 * @brief      Controlador de la gestión de los parámetros de Compras.
 *
 * @author     Argenis Osorio <asosorio@cenditel.gob.ve>
 *
 * @license   [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseParameterController extends Controller
{
    /**
     * Método que devuelve los parámetros de configuración de Compras: Número de
     * decimales a mostrar y Redondeo de cifras.
     *
     * @author    Argenis Osorio <asosorio@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $dataArray = Parameter::where([
            'active' => true,
            'required_by' => 'purchase',
        ])->orderBy('id')->get();

        $numberDecimals = '';
        $round = false;

        if (!empty($dataArray)) {
            foreach ($dataArray as $PurchaseReportConfiguration) {
                if ($PurchaseReportConfiguration['p_key'] == 'number_decimals') {
                    $numberDecimals = $PurchaseReportConfiguration['p_value'];
                } elseif ($PurchaseReportConfiguration['p_key'] == 'round') {
                    $round = ($PurchaseReportConfiguration['p_value'] === 'true');
                }
            }
        }

        return response()->json([
            "records" => [
                'number_decimals' => $numberDecimals,
                'round' => $round
            ]
        ]);
    }

    /**
     * Crea o Actualiza los parámetros de Número de decimales y redondear.
     *
     * @param     Request    $request    Datos de la petición
     *
     * @author     Argenis Osorio <asosorio@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\RedirectResponse
     */
    public function updateParameters(Request $request)
    {
        $parameters = ['number_decimals', 'round'];
        foreach ($parameters as $parameter) {
            if ($parameter == 'number_decimals') {
                Parameter::updateOrCreate(
                    [
                        'p_key' => $parameter,
                        'required_by' => 'purchase',
                        'active' => true
                    ],
                    [
                        'p_value' => $request->$parameter
                    ]
                );
            } else {
                Parameter::updateOrCreate(
                    [
                        'p_key' => $parameter,
                        'required_by' => 'purchase',
                        'active' => true
                    ],
                    [
                        'p_value' => (!is_null($request->$parameter)) ? 'true' : 'false'
                    ]
                );
            }
        }
        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->back();
    }
}
