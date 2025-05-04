<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPurchaseRequirementsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToPurchaseRequirementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requirements', function (Blueprint $table) {
            $table->foreignId('prepared_by_id')->nullable()
                ->constrained('payroll_employments')->onUpdate('cascade')->comment('Preparado por');
            $table->foreignId('reviewed_by_id')->nullable()
                ->constrained('payroll_employments')->onUpdate('cascade')->comment('Revisado por');
            $table->foreignId('verified_by_id')->nullable()
                ->constrained('payroll_employments')->onUpdate('cascade')->comment('Verificado por');
            $table->foreignId('first_signature_id')->nullable()
                ->constrained('payroll_employments')->onUpdate('cascade')->comment('Firmado por');
            $table->foreignId('second_signature_id')->nullable()
                ->constrained('payroll_employments')->onUpdate('cascade')->comment('Firmado por');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requirements', function (Blueprint $table) {
            $table->dropForeign(['prepared_by_id']);
            $table->dropColumn('prepared_by_id');

            $table->dropForeign(['reviewed_by_id']);
            $table->dropColumn('reviewed_by_id');

            $table->dropForeign(['verified_by_id']);
            $table->dropColumn('verified_by_id');

            $table->dropForeign(['first_signature_id']);
            $table->dropColumn('first_signature_id');

            $table->dropForeign(['second_signature_id']);
            $table->dropColumn('second_signature_id');
        });
    }
}
