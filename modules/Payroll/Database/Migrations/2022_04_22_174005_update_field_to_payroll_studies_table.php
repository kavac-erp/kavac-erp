<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPayrollStudiesTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToPayrollStudiesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_studies')) {
            Schema::table('payroll_studies', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_studies', 'graduation_year')) {
                    $table->dropColumn('graduation_year');
                }
            });

            Schema::table('payroll_studies', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_studies', 'graduation_year')) {
                    $table->date('graduation_year')->nullable();
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
        if (Schema::hasTable('payroll_studies')) {
            Schema::table('payroll_studies', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_studies', 'graduation_year')) {
                    $table->dropColumn('graduation_year');
                };
            });

            Schema::table('payroll_studies', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_studies', 'graduation_year')) {
                    $table->year('graduation_year')->nullable();
                };
            });
        }
    }
}
