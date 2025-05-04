<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleOrderManagementTable
 * @brief Migración encargada de crear la tabla para la gestión de pedidos de venta o órdenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleOrderManagementTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_order_management', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('Nombre o descripción de pedido');
            $table->string('cedule', 100)->comment('Cédula');
            $table->string('type', 100)->comment('Tipo');
            $table->string('code', 100)->comment('Código');
            $table->string('category', 100)->comment('Categoria');
            $table->string('quantity', 100)->comment('Cantidad');
            $table->enum('status', ['REVIEW', 'APPROVED','REJECTED'])->default('REVIEW')
                          ->comment(
                              'Determina el estatus del requerimiento
                              (REVIEW) - en Revisión.
                              (APPROVED) - Aprobado,
                              (REJECTED) - Rechazado',
                          );
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_order_management');
    }
}
