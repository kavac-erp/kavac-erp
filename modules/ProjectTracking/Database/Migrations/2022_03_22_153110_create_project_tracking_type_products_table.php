<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingTypeProductsTable
 * @brief Migración encargada de manejar la comunicacion con la base de datos y generar la tabla project_tracking_type_products
 *
 * Migración encargada de manejar la comunicacion con la base de datos y generar la tabla project_tracking_type_products
 *
 * @author    Francisco Escala <fjescala@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingTypeProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_type_products', function (Blueprint $table) {
            $table->id()->comment('Identificador único del registro');
            $table->string('name', 100)->comment('Nombre del producto');
            $table->string('description', 250)->comment('descripcion del producto');
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
        Schema::dropIfExists('project_tracking_type_products');
    }
}
