<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RemoveFieldToSaleWarehouseMovementsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveFieldToSaleWarehouseMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_warehouse_movements', function (Blueprint $table) {
            if (Schema::hasColumn('sale_warehouse_movements', 'history_tax_id')) {
                $table->dropForeign('sale_warehouse_movements_history_tax_id_foreign');
                $table->dropColumn('history_tax_id');
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
        Schema::table('sale_warehouse_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_warehouse_movements', 'history_tax_id')) {
                $table->foreignId('history_tax_id')->nullable()->comment('IVA')->constrained()->onUpdate('cascade')->onDelete('restrict');
            }
        });
    }
}
