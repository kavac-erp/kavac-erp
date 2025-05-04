<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToPayrollConceptsTable
 * @brief Migración para cambiar los campos de la tabla payroll_concepts
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToPayrollConceptsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_concepts', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_concepts', 'code')) {
                $table->dropColumn('code');
            };
            if (Schema::hasColumn('payroll_concepts', 'incidence_type')) {
                $table->dropColumn('incidence_type');
            };
            if (Schema::hasColumn('payroll_concepts', 'affect')) {
                $table->dropColumn('affect');
            };
        });
        Schema::table('payroll_concepts', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_concepts', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')->comment('Moneda');
            };
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_concepts', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_concepts', 'code')) {
                $table->string('code')->nullable()->comment('Código del concepto');
            };
            if (!Schema::hasColumn('payroll_concepts', 'incidence_type')) {
                $table->enum('incidence_type', ['value', 'absolute_value', 'tax_unit', 'percent'])
                      ->nullable()->comment('Tipo de incidencia del concepto: valor, ' .
                        'valor absoluto, unidad tributaria o porcentaje');
            };
            if (!Schema::hasColumn('payroll_concepts', 'affect')) {
                $table->enum('affect', ['base_salary', 'normal_salary', 'dialy_salary', 'comprehensive_salary'])
                      ->nullable()->comment('Incide sobre: salario base, salario normal, ' .
                        'salario diario, salario integral');
            };
        });
        Schema::table('payroll_concepts', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_concepts', 'currency_id')) {
                $table->dropForeign('payroll_concepts_currency_id_foreign');
            };
        });
    }
}
