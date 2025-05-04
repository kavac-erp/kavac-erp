<?php

/** [descripción del namespace] */

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseDirectHire;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

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
class PurchaseUpdateDirectHireStatusSeeder extends Seeder
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


        DB::transaction(function () {

            $directHires = PurchaseDirectHire::all();
            //Se pregunta si el módulo 'Budget' (Presupuesto) está habilitado
            $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

            if ($has_budget) {
                foreach ($directHires as $directHire) {
                    $find_compromise = \Modules\Budget\Models\BudgetCompromise::where('document_number', $directHire->code)->first();
                    if ($find_compromise) {
                        $directHire->status = "APPROVED";
                        $directHire->save();
                    }
                }
            }
        });
    }
}
