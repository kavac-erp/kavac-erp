<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceIndicatorsTable
 * @brief Crea la tabla de indicadores de servicios
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceIndicatorsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('citizen_service_indicators')) {
            Schema::create('citizen_service_indicators', function (Blueprint $table) {
                $table->id()->comment('Identificador único del registro');

                $table->string('name')->comment('Nombre del indicador');
                $table->string('description')->nullable()->comment('Descripción del indicador');
                $table->foreignId('effect_types_id')->references('id')->on('citizen_service_effect_types')->onDelete('restrict')->onUpdate('cascade')->nullable()->comment('Tipo de impacto');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citizen_service_indicators');
    }
}
