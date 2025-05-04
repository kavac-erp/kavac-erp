<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFieldConceptToFinanceBankingMovementsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'concept')) {
                $table->longText('concept')->nullable()->comment('Concepto')->change();
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
        Schema::table('finance_banking_movements', function (Blueprint $table) {
            if (Schema::hasColumn('finance_banking_movements', 'concept')) {
                $table->string('concept')->comment('Concepto')->change();
            }
        });
    }
}
