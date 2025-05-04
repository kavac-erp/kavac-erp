<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeleteFieldPurchaseProcessesIdToPurchasePlans
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldPurchaseProcessesIdToPurchasePlans extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('purchase_plans')) {
            Schema::table('purchase_plans', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_plans', 'purchase_processes_id')) {
                    $table->dropForeign(['purchase_processes_id']);
                    $table->dropColumn('purchase_processes_id');
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
        if (Schema::hasTable('purchase_plans')) {
            Schema::table('purchase_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_plans', 'purchase_processes_id')) {
                    $table->foreignId('purchase_processes_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
                }
            });
        }
    }
}
