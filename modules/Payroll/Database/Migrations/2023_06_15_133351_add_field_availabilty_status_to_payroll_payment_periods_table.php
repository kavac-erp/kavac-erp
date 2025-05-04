<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldAvailabiltyStatusToPayrollPaymentPeriodsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAvailabiltyStatusToPayrollPaymentPeriodsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_payment_periods', 'availability_status')) {
                $table->string('availability_status')->nullable()
                    ->comment('Indica si el pago del periodo se encuentra disponible');
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
        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_payment_periods', 'availability_status')) {
                $table->dropColumn('availability_status');
            }
        });
    }
}
