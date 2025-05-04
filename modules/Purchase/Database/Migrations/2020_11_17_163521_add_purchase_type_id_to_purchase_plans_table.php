<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddPurchaseTypeIdToPurchasePlansTable
 * @brief Migración encargada de agregar el campo purchase_type_id a la tabla purchase_plans
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddPurchaseTypeIdToPurchasePlansTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_plans', 'purchase_type_id')) {
                $table->foreignId('purchase_type_id')->nullable()->constrained()->onDelete('cascade')->comment(
                    'id del tipo de compra a relacionar con el registro'
                );
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
        Schema::table('purchase_plans', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_plans', 'purchase_type_id')) {
                $table->dropForeign(['purchase_type_id']);
                $table->dropColumn('purchase_type_id');
            }
        });
    }
}
