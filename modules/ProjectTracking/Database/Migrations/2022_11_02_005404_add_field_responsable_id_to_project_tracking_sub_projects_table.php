<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldResponsableIdToProjectTrackingSubProjectsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldResponsableIdToProjectTrackingSubProjectsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_sub_projects')) {
            Schema::table('project_tracking_sub_projects', function (Blueprint $table) {
                if (Schema::hasColumn('project_tracking_sub_projects', 'responsable')) {
                    $table->dropForeign('project_tracking_sub_projects_responsable_foreign');
                    $table->dropColumn('responsable');
                }
                if (!Schema::hasColumn('project_tracking_sub_projects', 'responsable_id')) {
                    $table->foreignId('responsable_id')->nullable()->references('id')->on('project_tracking_personal_registers')->onDelete('cascade')->onUpdate('cascade')->comment('Responsable del subproyecto');
                }
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
        if (Schema::hasTable('project_tracking_sub_projects')) {
            Schema::table('project_tracking_sub_projects', function (Blueprint $table) {
                $table->dropForeign('project_tracking_sub_projects_responsable_id_foreign');
                $table->dropColumn('responsable_id');
                $table->foreignId('responsable')->nullable()->references('id')->on('project_tracking_personal_registers')->onDelete('cascade')->onUpdate('cascade')->comment('Responsable del subproyecto');
            });
        }
    }
}
