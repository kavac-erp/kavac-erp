<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingProductsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->references('id')->on('project_tracking_projects')->onDelete('cascade')->onUpdate('cascade')->comment('Proyecto Asociado');
            $table->foreignId('subproject_id')->nullable()->references('id')->on('project_tracking_sub_projects')->onDelete('cascade')->onUpdate('cascade')->comment('Subproyecto Asociado');
            $table->string('name', 200)->comment('Nombre del Producto');
            $table->string('description', 200)->nullable()->comment('Descripción');
            $table->string('code', 100)->nullable()->comment('Código');
            $table->foreignId('dependency_id')->references('id')->on('project_tracking_dependencies')->onDelete('cascade')->onUpdate('cascade')->comment('Dependencias');
            $table->foreignId('responsable_id')->references('id')->on('project_tracking_personal_registers')->onDelete('cascade')->onUpdate('cascade')->comment('Responsable del Producto');
            $table->foreignId('type_product_id')->references('id')->on('project_tracking_type_products')->onDelete('cascade')->onUpdate('cascade')->comment('Tipo de producto');
            $table->date('start_date')->comment('Fecha de inicio');
            $table->date('end_date')->comment('Fecha de culminación');
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
        Schema::dropIfExists('project_tracking_products');
    }
}
