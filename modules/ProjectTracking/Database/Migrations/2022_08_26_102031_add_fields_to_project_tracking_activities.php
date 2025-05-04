<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToProjectTrackingActivities
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToProjectTrackingActivities extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @author Pedro Buitrago pbuitrago@cenditel.gob.ve
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_activities')) {
            Schema::table('project_tracking_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_activities', 'name')) {
                    $table->string('name')->comment('Nombre del proceso');
                }
                if (!Schema::hasColumn('project_tracking_activities', 'orden')) {
                    $table->string('orden')->comment('Orden del proceso');
                }
                if (!Schema::hasColumn('project_tracking_activities', 'name_activity')) {
                    $table->string('name_activity')->comment('Nombre de la actividad');
                }
                if (!Schema::hasColumn('project_tracking_activities', 'description')) {
                    $table->string('description')->nullable()->comment('Descripción de la actividad');
                }
                if (!Schema::hasColumn('project_tracking_activities', 'project_tracking_type_products_id')) {
                    $table->foreignId('project_tracking_type_products_id')->nullable()
                        ->comment('Identificador del tipo de producto')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
                }
                if (!Schema::hasColumn('project_tracking_activities', 'project_tracking_project_types_id')) {
                    $table->foreignId('project_tracking_project_types_id')->nullable()
                        ->comment('Identificador del tipo de proyecto')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
                }
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @author Pedro Buitrago pbuitrago@cenditel.gob.ve
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('project_tracking_activities')) {
            Schema::table('project_tracking_activities', function (Blueprint $table) {
                if (Schema::hasColumn('project_tracking_activities', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('project_tracking_activities', 'orden')) {
                    $table->dropColumn('orden');
                }
                if (Schema::hasColumn('project_tracking_activities', 'name_activity')) {
                    $table->dropColumn('name_activity');
                }
                if (Schema::hasColumn('project_tracking_activities', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('project_tracking_activities', 'project_tracking_type_products_id')) {
                    $table->dropForeign(['project_tracking_type_products_id']);
                    $table->dropColumn('project_tracking_type_products_id');
                }

                if (Schema::hasColumn('project_tracking_activities', 'project_tracking_project_types_id')) {
                    $table->dropForeign(['project_tracking_project_types_id']);
                    $table->dropColumn('project_tracking_project_types_id');
                }
            });
        }
    }
}
