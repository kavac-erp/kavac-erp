<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPayrollProfessionalsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldToPayrollProfessionalsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones (cambia las propiedades de la clave foranea )
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_professionals')) {
            Schema::table('payroll_professionals', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_professionals', 'payroll_staff_id')) {
                    $table->dropForeign(['payroll_staff_id']);
                }
            });

            Schema::table('payroll_professionals', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_professionals', 'payroll_staff_id')) {
                    $table->foreign('payroll_staff_id')->references('id')->on('payroll_staffs')->unique()->comment('Identificador del dato personal')->constrained()->onDelete('cascade')->onUpdate('cascade');
                }
            });
        }
    }

    /**
     * Método que revierte la migración
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_professionals')) {
            Schema::table('payroll_professionals', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_professionals', 'payroll_staff_id')) {
                    $table->dropForeign(['payroll_staff_id']);
                }
            });

            Schema::table('payroll_professionals', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_professionals', 'payroll_staff_id')) {
                    $table->foreign('payroll_staff_id')->references('id')->on('payroll_staffs')->unique()->comment('Identificador del dato personal')->constrained()->onUpdate('cascade')->onDelete('restrict');
                }
            });
        }
    }
}
