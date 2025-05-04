<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldQuantityToPurchasePivotModelsToRequirementItemsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldQuantityToPurchasePivotModelsToRequirementItemsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable("purchase_pivot_models_to_requirement_items")) {
            Schema::table("purchase_pivot_models_to_requirement_items", function (Blueprint $table) {
                if (!Schema::hasColumn("purchase_pivot_models_to_requirement_items", "quantity")) {
                    $table->float('quantity')->default(0)
                            ->nullable()
                            ->comment("Cantidad del producto a solicitar. Asignado en orden de compra");
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
        if (Schema::hasTable("purchase_pivot_models_to_requirement_items")) {
            Schema::table("purchase_pivot_models_to_requirement_items", function (Blueprint $table) {
                if (Schema::hasColumn("purchase_pivot_models_to_requirement_items", "quantity")) {
                    $table->dropColumn('quantity');
                }
            });
        }
    }
}
