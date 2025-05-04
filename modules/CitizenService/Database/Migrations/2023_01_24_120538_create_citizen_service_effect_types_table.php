<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateCitizenServiceEffectTypesTable
 * @brief Crea la tabla de tipos de impactos para las solicitudes de servicio
 *
 * @author Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateCitizenServiceEffectTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('citizen_service_effect_types')) {
            Schema::create('citizen_service_effect_types', function (Blueprint $table) {
                $table->id()->comment('Identificador único del registro');

                $table->string('name')->comment('Nombre del tipo de impacto');
                $table->string('description')->nullable()->comment('Descripción del tipo de impacto');

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
        Schema::dropIfExists('citizen_service_effect_types');
    }
}
