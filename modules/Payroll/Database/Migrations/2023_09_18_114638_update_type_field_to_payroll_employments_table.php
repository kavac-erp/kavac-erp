<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldToPayrollEmploymentsTable
 *
 * @brief Actualiza el tipo de dato del campo de la tabla "payroll_employments"
 * para que sea de tipo string, antes era entero
 *
 * Cambia el tipo de datos del campo de entero a string
 *
 * @author Natanael Rojo <rojonatanael99@gmail.com>
 *
 * @license [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateTypeFieldToPayrollEmploymentsTable extends Migration
{
    /**
     * Ejecuta la migración, actualiza el tipo de dato del campo worksheet_code
     * a tipo string, antes era integer.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_employments')) {
            Schema::table('payroll_employments', function (Blueprint $table) {
                $table->string('worksheet_code')->change();
            });
        }
    }

    /**
     * Revierte la migración, actualiza el tipo de dato del campo worksheet_code
     * a tipo integer como estaba anteriormente.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('payroll_employments')) {
            Schema::table('payroll_employments', function (Blueprint $table) {
                $table->integer('worksheet_code')->change();
            });
        }
    }
}
