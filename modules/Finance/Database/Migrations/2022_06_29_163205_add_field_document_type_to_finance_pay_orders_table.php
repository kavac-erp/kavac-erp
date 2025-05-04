<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldDocumentTypeToFinancePayOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_pay_orders', 'document_type')) {
                $table->string('document_type')->default('C')->comment('Tipo de documento de la orden de pago');
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
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (Schema::hasColumn('finance_pay_orders', 'document_type')) {
                $table->dropColumn('document_type');
            }
        });
    }
}
