<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Finance\Models\FinancePaymentExecute;
use Modules\Finance\Models\FinancePayOrder;
use Modules\Finance\Models\FinancePayOrderFinancePaymentExecute;

/**
 * @class FinanceUpdateStatusPayOrdersTableSeeder
 * @brief Actualiza el estatus de las ordenes de pago
 *
 * Clase seeder para actualizar el estatus de las ordenes de pago
 *
 * @author Francisco J. P. Ruíz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceUpdateStatusPayOrdersTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
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
