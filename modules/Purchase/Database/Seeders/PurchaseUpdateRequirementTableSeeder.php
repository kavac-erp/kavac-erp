<?php

/** [descripción del namespace] */

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseRequirement;

/**
 * @class $CLASS$
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseUpdateRequirementTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @method run
     *
     * @return void     [descripción de los datos devueltos]
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
                $requirement->requirement_status = 'WAIT';
                $requirement->save();
            }
        }
    }
}
