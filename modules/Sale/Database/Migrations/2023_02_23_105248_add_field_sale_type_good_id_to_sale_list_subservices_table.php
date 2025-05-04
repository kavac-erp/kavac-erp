<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldSaleTypeGoodIdToSaleListSubservices
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldSaleTypeGoodIdToSaleListSubservicesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('sale_list_subservices', function (Blueprint $table) {
                $table->foreignId('sale_type_good')->nullable()->constrained()->onUpdate('cascade')->onDelete('restrict')->references('id')->on('sale_type_goods')->comment('Tipo de Servicio')->change();
            });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_list_subservices', function (Blueprint $table) {
            if (Schema::hasColumn('sale_type_good', 'sale_list_subservices')) {
                $table->dropForeign(['sale_type_good']);
                $table->dropColumn('sale_type_good');
            }
        });
    }
}
