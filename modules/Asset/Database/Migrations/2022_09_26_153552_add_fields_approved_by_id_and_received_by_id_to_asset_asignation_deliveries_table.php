<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsApprovedByIdAndReceivedByIdToAssetAsignationDeliveriesTable
 * @brief Agrega campos adicionales a la tabla asset_asignation_deliveries
 *
 * Agrega campos adicionales a la tabla asset_asignation_deliveries
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsApprovedByIdAndReceivedByIdToAssetAsignationDeliveriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_asignation_deliveries', function (Blueprint $table) {
            $table->foreignId('approved_by_id')->nullable()
                ->constrained('payroll_staffs')->onUpdate('cascade')->comment('Aprobado por');
            $table->foreignId('received_by_id')->nullable()
                ->constrained('payroll_staffs')->onUpdate('cascade')->comment('Recibido por');
            $table->json('ids_assets')->nullable()
                ->comment('Lista de ids. de los bienes entregados');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_asignation_deliveries', function (Blueprint $table) {
            $table->dropForeign(['approved_by_id']);
            $table->dropColumn('approved_by_id');

            $table->dropForeign(['received_by_id']);
            $table->dropColumn('received_by_id');

            $table->dropColumn('ids_assets');
        });
    }
}
