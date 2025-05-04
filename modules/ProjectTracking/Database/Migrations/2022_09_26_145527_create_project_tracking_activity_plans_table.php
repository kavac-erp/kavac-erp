<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingActivityPlansTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingActivityPlansTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_activity_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_name')->nullable()->references('id')->on('project_tracking_projects')->onDelete('cascade')->onUpdate('cascade')->comment('Proyecto');
            $table->foreignId('subproject_name')->nullable()->references('id')->on('project_tracking_sub_projects')->onDelete('cascade')->onUpdate('cascade')->comment('Subproyecto');
            $table->foreignId('product_name')->nullable()->references('id')->on('project_tracking_products')->onDelete('cascade')->onUpdate('cascade')->comment('Producto');
            $table->foreignId('institution_id')->references('id')->on('institutions')->onDelete('cascade')->onUpdate('cascade')->comment('Institución');
            $table->string('execution_year')->comment('Año de ejecución');
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
        Schema::dropIfExists('project_tracking_activity_plans');
    }
}
