<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseQuotation;

/**
 * @class PurchaseUpdateQuotationStatusSeeder
 * @brief Actualiza el estatus de las cotizaciones en el módulo de compras
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseUpdateQuotationStatusSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $quotations = PurchaseQuotation::all();

        foreach ($quotations as $quotation) {
            if ($quotation->orderable_id) {
                $quotation->status = 'APPROVED';
                $quotation->save();
            }
        }
    }
}
