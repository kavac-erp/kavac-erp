<?php

/** [descripción del namespace] */

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Models\FinancePaymentExecute;
use Modules\Finance\Models\FinancePayOrder;
use Modules\Finance\Models\FinancePayOrderFinancePaymentExecute;

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
class FinanceUpdateStatusPayOrdersTableSeeder extends Seeder
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

        $financePaymentExecute = FinancePaymentExecute::all();

        foreach ($financePaymentExecute as $PaymentExecute) {
            if ($PaymentExecute->status == 'PA') {
                $payOrdersPaymentExecute = FinancePayOrderFinancePaymentExecute::where('finance_payment_execute_id', $PaymentExecute->id)->get();
                if ($payOrdersPaymentExecute) {
                    foreach ($payOrdersPaymentExecute as $payOrder) {
                        $pay_order = FinancePayOrder::find($payOrder->finance_pay_order_id);
                        if ($pay_order) {
                            $pay_order->status = 'PA';
                            $pay_order->save();
                        }
                    }
                }
            }
        }
    }
}
