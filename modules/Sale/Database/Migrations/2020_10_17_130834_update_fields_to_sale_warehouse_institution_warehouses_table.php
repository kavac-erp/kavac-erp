<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToSaleWarehouseInstitutionWarehousesTable
 * @brief Migración encargada de modificar los campos de la tabla de almacenes del modulo de comercialización
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToSaleWarehouseInstitutionWarehousesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_warehouse_institution_warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('sale_warehouse_institution_warehouses', 'sale_warehouses_id')) {
                $table->dropForeign(['sale_warehouses_id']);
                $table->dropColumn('sale_warehouses_id');
            };
            if (!Schema::hasColumn('sale_warehouse_institution_warehouses', 'sale_warehouse_id')) {
                $table->foreignId('sale_warehouse_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        Schema::table('sale_warehouse_institution_warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_warehouse_institution_warehouses', 'sale_warehouse_id')) {
                $table->foreignId('sale_warehouse_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
