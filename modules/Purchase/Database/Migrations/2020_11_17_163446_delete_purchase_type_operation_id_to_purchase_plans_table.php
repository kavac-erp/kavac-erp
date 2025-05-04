<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DeletePurchaseTypeOperationIdToPurchasePlansTable
 * @brief Migración encargada de borrar el campo purchase_type_operation_id de la tabla purchase_plans
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeletePurchaseTypeOperationIdToPurchasePlansTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_plans', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_plans', 'purchase_type_operation_id')) {
                $table->dropForeign(['purchase_type_operation_id']);
                $table->dropColumn('purchase_type_operation_id');
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        // No necesita accion reversa
    }
}
