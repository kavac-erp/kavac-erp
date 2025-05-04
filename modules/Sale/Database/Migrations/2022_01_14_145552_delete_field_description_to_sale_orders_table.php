<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class DeleteFieldDescriptionToSaleOrdersTable
 * @brief Migración encargada de eliminar el campo descripción de la tabla de ordenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteFieldDescriptionToSaleOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sale_orders')) {
            Schema::table('sale_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_orders', 'description')) {
                    $table->dropColumn(['description']);
                }
                if (!Schema::hasColumn('sale_orders', 'status')) {
                    $table->dropColumn(['status']);
                    $table->text('status')->nullable()->comment('Estado de la solicitud');
                }
                if (!Schema::hasColumn('sale_orders', 'id_number')) {
                    $table->dropColumn(['id_number']);
                    $table->text('id_number')->nullable()->comment('Cedula o Rif de la persona');
                }
                if (!Schema::hasColumn('sale_orders', 'type_person')) {
                    $table->text('type_person')->nullable()->comment('Tipo de persona que realiza la solicitud');
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
        if (Schema::hasTable('sale_orders')) {
            Schema::table('sale_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_orders', 'description')) {
                    $table->dropColumn(['description']);
                }
            });
        }
    }
}
