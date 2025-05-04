<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldDescriptionToFinancePaymentExecutesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_payment_executes', function (Blueprint $table) {
            Schema::table('finance_payment_executes', function (Blueprint $table) {
                if (!Schema::hasColumn('finance_payment_executes', 'description')) {
                    $table->string('description', 300)->nullable()
                    ->comment('Descripción del motivo de la anulación del registro');
                };
            });
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_payment_executes', function (Blueprint $table) {
            if (Schema::hasColumn('finance_payment_executes', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}
