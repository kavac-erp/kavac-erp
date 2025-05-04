<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Purchase\Models\PurchaseRequirementItem;
use Nwidart\Modules\Facades\Module;

/**
 * @class ChangeFieldsToPurchaseRequirementItemsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldsToPurchaseRequirementItemsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_requirement_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requirement_items', 'purchase_product_id')) {
                $table->foreignId('purchase_product_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('purchase_requirement_items', 'history_tax_id')) {
                $table->foreignId('history_tax_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
            if (!Schema::hasColumn('purchase_requirement_items', 'measurement_unit_id')) {
                $table->foreignId('measurement_unit_id')->nullable()->constrained()->onDelete('restrict')->onUpdate('cascade');
            }
        });

        /**
         * Se consultan los registros de la tabla PurchaseRequerimentItem con los registros anteriores,
         * para tomar el los impuestos y agregarlos a los nuevos campos.
         */
        if (Module::has('Warehouse')) {
            $products = PurchaseRequirementItem::with('warehouseProduct')->get();
            foreach ($products as $product) {
                if ($product->warehouseProduct) {
                    $product['history_tax_id'] = $product->warehouseProduct->history_tax_id;
                    $product['measurement_unit_id'] = $product->warehouseProduct->measurement_unit_id;
                    $product->save();
                }
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requirement_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requirement_items', 'purchase_product_id')) {
                $table->dropForeign('purchase_requirement_items_purchase_product_id_foreign');
                $table->dropColumn('purchase_product_id');
            }
            if (Schema::hasColumn('purchase_requirement_items', 'history_tax_id')) {
                $table->dropForeign('purchase_requirement_items_history_tax_id_foreign');
                $table->dropColumn('history_tax_id');
            }
            if (Schema::hasColumn('purchase_requirement_items', 'measurement_unit_id')) {
                $table->dropForeign('purchase_requirement_items_measurement_unit_id_foreign');
                $table->dropColumn('measurement_unit_id');
            }
        });
    }
}
