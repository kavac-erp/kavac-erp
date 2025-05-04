<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateProjectTrackingProjectsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateProjectTrackingProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tracking_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->comment('Nombre del Proyecto');
            $table->string('description', 200)->comment('Descripción');
            $table->foreignId('project_type_id')->references('id')->on('project_tracking_project_types')->onDelete('cascade')->onUpdate('cascade')->comment('Tipo de Proyecto');
            $table->string('code', 100)->comment('Código');
            $table->foreignId('dependency_id')->references('id')->on('project_tracking_dependencies')->onDelete('cascade')->onUpdate('cascade')->comment('Dependencias');
            $table->foreignId('responsable_id')->references('id')->on('project_tracking_personal_registers')->onDelete('cascade')->onUpdate('cascade')->comment('Responsable del Proyecto');
            $table->foreignId('type_product_id')->references('id')->on('project_tracking_type_products')->onDelete('cascade')->onUpdate('cascade')->comment('Tipo de producto');
            $table->date('start_date')->comment('Fecha de inicio');
            $table->date('end_date')->comment('Fecha de culminación');
            $table->bigInteger('financing_amount')->nullable()->comment('Monto de Financiamiento');
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
        Schema::dropIfExists('project_tracking_projects');
    }
}
