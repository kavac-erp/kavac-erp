<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Models\FinancePaymentExecute;

/**
 * @class FinanceUpdateDeductedAtPaymentDeductiosTableSeeder
 * @brief Modifica los datos de la ejecución de pagos
 *
 * Clase seeder para modificar los datos de la ejecución de pagos
 *
 * @author Francisco J. P. Ruíz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceUpdateDeductedAtPaymentDeductiosTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $financePaymentExecute = FinancePaymentExecute::query()
        ->whereHas('financePaymentDeductions', function ($query) {
            $query->whereNull('deducted_at');
        })->get();

        $financePaymentExecute->each(function ($PaymentExecute) {
            $PaymentExecute->financePaymentDeductions()->get()->each(function ($paymetDeduction) use ($PaymentExecute) {
                $paymetDeduction->deducted_at = $PaymentExecute->paid_at;
                $paymetDeduction->save();
            });
        });
    }
}
