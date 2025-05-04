<?php
/** [descripción del namespace] */
namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Models\FinancePaymentExecute;

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
class FinanceUpdateDeductedAtPaymentDeductiosTableSeeder extends Seeder
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

        $financePaymentExecute = FinancePaymentExecute::query()
        ->whereHas('financePaymentDeductions', function ($query) {
            $query->whereNull('deducted_at');
        })->get();

        $financePaymentExecute->each(function($PaymentExecute){
            $PaymentExecute->financePaymentDeductions()->get()->each(function($paymetDeduction) use($PaymentExecute){
                $paymetDeduction->deducted_at = $PaymentExecute->paid_at;
                $paymetDeduction->save();
            });
        });
    }
}
