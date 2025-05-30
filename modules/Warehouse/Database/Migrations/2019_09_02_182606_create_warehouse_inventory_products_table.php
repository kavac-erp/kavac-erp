<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWarehouseInventoryProductsTable
 * @brief Crear tabla de inventario de productos
 *
 * Gestiona la creación o eliminación de la tabla de inventario de productos
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWarehouseInventoryProductsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author  Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('warehouse_inventory_products')) {
            Schema::create('warehouse_inventory_products', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');
                $table->string('code', 20)->unique()->comment('Código identificador del producto en el inventario');
                $table->integer('exist')->unsigned()->nullable()
                      ->comment('Cantidad de productos en existencia, incluye los reservados por solicitudes almacén');
                $table->integer('reserved')->unsigned()->nullable()
                      ->comment('Cantidad de productos reservados por solicitudes de almacén');
                $table->float('unit_value')->unsigned()->comment('Valor por unidad del producto en el almacén');

                $table->foreignId('currency_id')->nullable()->constrained()->onUpdate('cascade');

                $table->foreignId('warehouse_product_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('measurement_unit_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->unsignedBigInteger('warehouse_institution_warehouse_id')->nullable()
                      ->comment('Identificador único de la institución que gestiona el almacén donde está el producto');
                $table->foreign(
                    'warehouse_institution_warehouse_id',
                    'warehouse_inventory_products_institution_warehouse_pk'
                )->references('id')->on(
                    'warehouse_institution_warehouses'
                )->onDelete('restrict')->onUpdate('cascade');


                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_inventory_products');
    }
}
