<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseRequirement;

/**
 * @class PurchaseUpdateRequirementTableSeeder
 * @brief Actualiza el campo requirement_status de la tabla purchase_requirements
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseUpdateRequirementTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $requirements = PurchaseRequirement::with(['purchaseBaseBudget'])->get();

        foreach ($requirements as $requirement) {
            if (
                $requirement->purchaseBaseBudget->status_aux != 'PARTIALLY_QUOTED'
                && $requirement->purchaseBaseBudget->status_aux != 'QUOTED'
                && $requirement->purchaseBaseBudget->status_aux != 'BOUGHT'
            ) {
                $requirement['requirement_status'] = 'WAIT';
                $requirement->save();
            }
        }
    }
}
