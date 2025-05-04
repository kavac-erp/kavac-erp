<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Nwidart\Modules\Facades\Module;

/**
 * @class UpdateFieldsToProjectTrackingProductsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToProjectTrackingProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('project_tracking_products')) {
            Schema::table('project_tracking_products', function (Blueprint $table) {
                if (Schema::hasColumn('project_tracking_products', 'responsable_id')) {
                    $table->dropForeign('project_tracking_products_responsable_id_foreign');
                    $table->dropColumn('responsable_id');
                }
            });
        }
        if (Schema::hasTable('project_tracking_products')) {
            Schema::table('project_tracking_products', function (Blueprint $table) {
                if (!Schema::hasColumn('project_tracking_products', 'responsable_id')) {
                    if (Module::has('Payroll')) {
                        $table->foreignId('responsable_id')->references('id')->on('payroll_staffs')->onDelete('restrict')
                        ->onUpdate('cascade')->comment('Responsable del producto');
                    } else {
                        $table->foreignId('responsable_id')->references('id')->on('project_tracking_personal_registers')->onDelete('restrict')
                        ->onUpdate('cascade')->comment('Responsable del producto');
                    }
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
        Schema::table('project_tracking_products', function (Blueprint $table) {
        });
    }
}
