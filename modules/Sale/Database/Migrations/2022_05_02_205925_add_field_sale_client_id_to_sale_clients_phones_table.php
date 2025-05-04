<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class AddFieldSaleClientIdToSaleClientsPhonesTable
 * @brief Migración encargada de agregar el campo sale_client_id a la tabla de telefonos de clientes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldSaleClientIdToSaleClientsPhonesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_clients_phones', function (Blueprint $table) {
             $table->foreignId('sale_client_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_clients_phones', function (Blueprint $table) {
            if (Schema::hasColumn('sale_clients_phones', 'sale_client_id')) {
                $table->dropForeign(['sale_client_id']);
                $table->dropColumn('sale_client_id');
            }
        });
    }
}
