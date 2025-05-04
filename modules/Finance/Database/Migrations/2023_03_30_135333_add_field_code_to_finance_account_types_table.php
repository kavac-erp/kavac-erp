<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldCodeToFinanceAccountTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_account_types', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->comment('Código asociado a la tipo de cuenta bancaria');
            ;
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_account_types', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
}
