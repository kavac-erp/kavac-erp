<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\HistoryTax;
use \Modules\Warehouse\Models\WarehouseProduct;

/**
 * @class UpdateFieldTaxIdToWarehouseProductsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldTaxIdToWarehouseProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouse_products')) {
            Schema::table('warehouse_products', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_products', 'tax_id')) {
                    $table->foreignId('history_tax_id')->nullable()
                          ->comment('Identificador único asociado al impuesto')
                          ->constrained('history_taxes')->onDelete('restrict')->onUpdate('cascade');
                }
            });

            Schema::table('warehouse_products', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_products', 'tax_id')) {
                    $products = WarehouseProduct::get()->pluck('tax_id')->toArray();

                    foreach ($products as $taxId) {
                        if ($taxId !== null) {
                            $historyId = HistoryTax::where('tax_id', $taxId)->orderBy('id', 'desc')->first()->id;
                            DB::table('warehouse_products')->where('tax_id', $taxId)->update(['history_tax_id' => $historyId]);
                        }
                    }

                    $table->dropForeign('warehouse_products_tax_id_foreign');
                    $table->dropColumn('tax_id');
                };
            });
        };
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('warehouse_products')) {
            Schema::table('warehouse_products', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouse_products', 'tax_id')) {
                    $table->foreignId('tax_id')->nullable()
                          ->comment('Identificador único asociado al impuesto')
                          ->constrained()->onDelete('restrict')->onUpdate('cascade');
                };
            });

            Schema::table('warehouse_products', function (Blueprint $table) {
                if (Schema::hasColumn('warehouse_products', 'history_tax_id')) {
                    $table->dropForeign('warehouse_products_history_tax_id_foreign');
                    $table->dropColumn('history_tax_id');
                };
            });
        };
    }
}
