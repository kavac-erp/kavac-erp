<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldDocumentStatusIdToFinancePayOrdersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_pay_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_pay_orders', 'document_status_id')) {
                $table->foreignId('document_status_id')->nullable()
                    ->comment('Identificador único asociado al estatus del documento')->constrained('document_status')
                    ->onDelete('restrict')->onUpdate('cascade');
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
            if (Schema::hasColumn('finance_pay_orders', 'document_status_id')) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
            }
        });
    }
}
