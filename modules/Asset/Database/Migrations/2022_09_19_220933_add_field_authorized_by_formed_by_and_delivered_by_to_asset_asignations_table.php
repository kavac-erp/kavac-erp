<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldAuthorizedByFormedByAndDeliveredByToAssetAsignationsTable
 * @brief Agrega campos authorized_by_id, formed_by_id y delivered_by_id a la tabla asset_asignations
 *
 * Agrega campos authorized_by_id, formed_by_id y delivered_by_id a la tabla asset_asignations
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAuthorizedByFormedByAndDeliveredByToAssetAsignationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_asignations', function (Blueprint $table) {
            $table->foreignId('authorized_by_id')->nullable()
                ->constrained('payroll_staffs')->onUpdate('cascade')->comment('Autorizado por');
            $table->foreignId('formed_by_id')->nullable()
                ->constrained('payroll_staffs')->onUpdate('cascade')->comment('Conformado por');
            $table->foreignId('delivered_by_id')->nullable()
                ->constrained('payroll_staffs')->onUpdate('cascade')->comment('Entregado por');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_asignations', function (Blueprint $table) {
            $table->dropForeign(['authorized_by_id']);
            $table->dropColumn('authorized_by_id');

            $table->dropForeign(['formed_by_id']);
            $table->dropColumn('formed_by_id');

            $table->dropForeign(['delivered_by_id']);
            $table->dropColumn('delivered_by_id');
        });
    }
}
