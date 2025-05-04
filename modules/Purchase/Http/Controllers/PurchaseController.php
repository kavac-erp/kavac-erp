<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Purchase\Models\FiscalYear;

/**
 * @class PurchaseController
 * @brief Controlador para la gestión del módulo de compras
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseController extends Controller
{
    /**
     * Obtiene el año fiscal actual
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFiscalYear()
    {
        $fiscal_year = FiscalYear::where('active', true)->first();
        return response()->json(['fiscal_year' => $fiscal_year, 'message' => 'success'], 200);
    }

    /**
     * Obtiene un listado de las instituciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstitutions()
    {
        $institutions = template_choices('App\Models\Institution', 'name', [], true);
        return response()->json(['institutions' => $institutions, 'message' => 'success'], 200);
    }
}
