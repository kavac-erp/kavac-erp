<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateSaleWarehouseMovements
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateSaleWarehouseMovements extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_warehouse_movements', function (Blueprint $table) {
            if (Schema::hasTable('sale_warehouse_movements')) {
                $table->foreignId('history_tax_id')->nullable()->comment('IVA')->constrained()->onUpdate('cascade')->onDelete('restrict');
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
        DB::statement("DROP TABLE IF EXISTS sale_warehouse_movements CASCADE");
        Schema::dropIfExists('sale_warehouse_movements');
    }
}
