<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateSaleGoodToBeTradedPayrollStaffTable
 * @brief Tabla pivote entre SaleGoodsToBeTraded y PayrollStaff
 *
 * Tabla pivote entre SaleGoodsToBeTraded y PayrollStaff
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateSaleGoodToBeTradedPayrollStaffTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_good_to_be_traded_payroll_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_goods_to_be_traded_id')->constrained()
                ->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('payroll_staff_id')->constrained()
                ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_good_to_be_traded_payroll_staff');
    }
}
