<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeletePayrollPaymentTypeIdToPayrollConceptAssignOptionsTable
 * @brief Migración para eliminar el campo de tipo de pago de la tabla de asignación de conceptos de nómina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeletePayrollPaymentTypeIdToPayrollConceptAssignOptionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_concept_assign_options', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_concept_assign_options', 'payroll_concept_id')) {
                $table->dropForeign(['payroll_concept_id']);
                $table->dropColumn('payroll_concept_id');
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
        Schema::table('payroll_concept_assign_options', function (Blueprint $table) {
            $table->foreignId('payroll_concept_id')->nullable()->constrained()
                ->onDelete('SET NULL')->onUpdate('cascade');
        });
    }
}
