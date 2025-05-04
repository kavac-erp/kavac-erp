<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldToPayrollPaymentTypesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldReceiptToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_payment_types', 'receipt')) {
                $table->boolean('receipt')->default(false)->nullable()
                    ->comment('Indica si se deben generar los recibos de pago para el tipo de pago');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_payment_types', 'receipt')) {
                $table->dropColumn('receipt');
            }
        });
    }
}
