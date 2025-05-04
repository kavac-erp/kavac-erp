<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class ChangeFieldIdNumberToSaleOrdersTable
 * @brief Migración encargada de cambiar el campo id_number de la tabla de ordenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ChangeFieldIdNumberToSaleOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sale_orders', 'id_number')) {
                $table->string('id_number')->comment('Cedula o Rif de la persona')->change();
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
        Schema::table('sale_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_orders', 'id_number')) {
                $table->json('id_number')->nullable()->comment('Status de la solicitud');
            }
        });
    }
}
