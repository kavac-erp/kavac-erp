<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToSaleTechnicalProposalsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToSaleTechnicalProposalsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_technical_proposals', function (Blueprint $table) {
            if (Schema::hasColumn('sale_technical_proposals', 'asset_asignations')) {
                $table->dropColumn('asset_asignations');
            };
            $table->string('status', 20)->nullable()->comment('Estatus de la propuesta');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_technical_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_technical_proposals', 'asset_asignations')) {
                $table->text('asset_asignations')->nullable()->comment('Bienes asignados');
            };
            if (Schema::hasColumn('sale_technical_proposals', 'status')) {
                $table->dropColumn('status');
            };
        });
    }
}
