<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldStatusPayroll extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payrolls')) {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status')->nullable()->comment('Estatus de la nomina referente a Disponibilidad');
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
        if (Schema::hasTable('payrolls')) {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        }
    }
}
