<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAssetAsignationDeliveriesTable
 * @brief Crear tabla de las solicitudes de entrega de bienes institucionales asignados
 *
 * Gestiona la creación o eliminación de la tabla de solicitudes de entrega de bienes institucionales asignados
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetAsignationDeliveriesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('asset_asignation_deliveries')) {
            Schema::create('asset_asignation_deliveries', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->string('state')->nullable()->comment('Estado de la solictud de entrega');
                $table->text('observation')->nullable()->comment('Observaciones de la entrega');

                $table->foreignId('asset_asignation_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->timestamps();
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
        Schema::dropIfExists('asset_asignation_deliveries');
    }
}
