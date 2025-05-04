<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPayrollPaymentTypesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldOrderToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_payment_types', 'order')) {
                $table->boolean('order')->default(true)->nullable()
                    ->comment('Indica si se debe generar una orden de pago despues de procesar el pago');
            }
            if (!Schema::hasColumn('payroll_payment_types', 'individual')) {
                $table->boolean('individual')->default(false)->nullable()
                    ->comment('Indica si las ordenes de pago a generar son generales o individual por cada beneficiario');
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
            if (Schema::hasColumn('payroll_payment_types', 'order')) {
                $table->dropColumn('order');
            }
            if (Schema::hasColumn('payroll_payment_types', 'individual')) {
                $table->dropColumn('individual');
            }
        });
    }
}
