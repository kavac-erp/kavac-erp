<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldSaleSettingProductTypeIdToSaleSettingProductsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldSaleSettingProductTypeIdToSaleSettingProductsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sale_setting_products')) {
            Schema::table('sale_setting_products', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_setting_products', 'sale_setting_product_type_id')) {
                    $table->foreignId('sale_setting_product_type_id')->nullable()
                        ->comment('Identificador del tipo de producto')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
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
        Schema::table('sale_setting_products', function (Blueprint $table) {
            if (Schema::hasColumn('sale_setting_products', 'sale_setting_product_type_id')) {
                $table->dropForeign(['sale_setting_product_type_id']);
                $table->dropColumn('sale_setting_product_type_id');
            }
        });
    }
}
