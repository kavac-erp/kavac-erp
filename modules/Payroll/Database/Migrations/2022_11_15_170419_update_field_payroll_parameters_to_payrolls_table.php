<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldPayrollParametersToPayrollsTable
 * @brief Actualiza el campo payroll_parameters de la tabla payrolls
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldPayrollParametersToPayrollsTable extends Migration
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
            });
        }
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
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
                }
            });
        }
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (!Schema::hasColumn('payrolls', 'payroll_parameters')) {
                    $table->string('payroll_parameters')->nullable()
                    ->comment('Valor establecido para los parámetros de nómina');
                }
            });
        }
    }
}
