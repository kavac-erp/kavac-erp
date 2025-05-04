<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceAddIndicatorsTable
 * @brief Crea la tabla de agregar indicadores
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceAddIndicatorsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citizen_service_add_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre del indicador');
            $table->foreignId('indicator_id')
                ->references('id')
                ->on('citizen_service_indicators')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->nullable()
                ->comment('Indicador');
            $table->foreignId('request_id')
                ->references('id')
                ->on('citizen_service_requests')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->nullable()
                ->comment('Solicitudes');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citizen_service_add_indicators');
    }
}
