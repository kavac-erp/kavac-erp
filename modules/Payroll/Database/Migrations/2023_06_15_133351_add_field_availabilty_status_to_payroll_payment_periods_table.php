<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldAvailabiltyStatusToPayrollPaymentPeriodsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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
