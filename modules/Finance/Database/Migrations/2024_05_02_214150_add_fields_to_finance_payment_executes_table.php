<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Finance\Models\FinancePaymentExecute;
use Modules\Finance\Models\FinancePayOrder;

class AddFieldsToFinancePaymentExecutesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_payment_executes', function (Blueprint $table) {
            $table->foreignId('finance_payment_method_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('finance_bank_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
        });

        $payOrders = FinancePayOrder::get();

        foreach ($payOrders as $payOrder) {
            foreach ($payOrder->financePaymentExecute as $paymentExecute) {
                $paymentExecute->finance_payment_method_id = $payOrder->finance_payment_method_id;
                $paymentExecute->finance_bank_account_id = $payOrder->finance_bank_account_id;
                $paymentExecute->save();
            }
        }

        Schema::table('finance_pay_orders', function (Blueprint $table) {
            $table->dropForeign(['finance_payment_method_id']);
            $table->dropForeign(['finance_bank_account_id']);
            $table->dropColumn(['finance_payment_method_id', 'finance_bank_account_id']);
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            $table->foreignId('finance_payment_method_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('finance_bank_account_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
        });

        $paymentExecutes = FinancePaymentExecute::get();

        foreach ($paymentExecutes as $paymentExecute) {
            foreach ($paymentExecute->financePayOrders as $payOrder) {
                $payOrder->finance_payment_method_id = $paymentExecute->finance_payment_method_id;
                $payOrder->finance_bank_account_id = $paymentExecute->finance_bank_account_id;
                $payOrder->save();
            }
        }

        Schema::table('finance_payment_executes', function (Blueprint $table) {
            $table->dropForeign(['finance_payment_method_id']);
            $table->dropForeign(['finance_bank_account_id']);
            $table->dropColumn(['finance_payment_method_id', 'finance_bank_account_id']);
        });
    }
}
