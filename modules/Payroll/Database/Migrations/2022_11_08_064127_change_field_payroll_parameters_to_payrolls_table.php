<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class ChangeFieldPayrollParametersToPayrollsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldPayrollParametersToPayrollsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('payrolls', 'payroll_parameters')) {
                    $table->dropColumn('payroll_parameters');
                }
                if (!Schema::hasColumn('payrolls', 'payroll_parameters')) {
                    $table->json('payroll_parameters')->nullable()
                    ->comment('Valor establecido para los parámetros de nómina');
                }
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('payrolls', 'payroll_parameters')) {
                    if (Schema::hasColumn('payrolls', 'payroll_parameters')) {
                        $table->dropColumn('payroll_parameters');
                    }
                    if (!Schema::hasColumn('payrolls', 'payroll_parameters')) {
                        $table->string('payroll_parameters')->nullable()
                        ->comment('Valor establecido para los parámetros de nómina');
                    }
                }
            });
        }
    }
}
