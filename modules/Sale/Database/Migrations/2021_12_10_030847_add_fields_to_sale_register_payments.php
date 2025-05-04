<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToSaleRegisterPayments
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToSaleRegisterPayments extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sale_register_payments')) {
            Schema::table('sale_register_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_register_payments', 'total_amount')) {
                    $table->string('total_amount', 100)->nullable()->comment('Monto total a pagar');
                    $table->string('way_to_pay', 100)->nullable()->comment('Forma de pago');
                    $table->string('banking_entity', 100)->nullable()->comment('Entidad bancaria');
                    $table->boolean('payment_approve')->default(false)->comment('Establecer pago aprobados. (true) si, (false) no');
                    $table->boolean('payment_refuse')->default(false)->comment('Establecer pago rechazado. (true) si, (false) no');
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
        Schema::table('sale_register_payments', function (Blueprint $table) {
            if (Schema::hasColumn('sale_register_payments', 'total_amount')) {
                $table->dropColumn('total_amount');
                $table->dropColumn('way_to_pay');
                $table->dropColumn('banking_entity');
                $table->dropColumn('payment_approve');
                $table->dropColumn('payment_refuse');
            }
        });
    }
}
