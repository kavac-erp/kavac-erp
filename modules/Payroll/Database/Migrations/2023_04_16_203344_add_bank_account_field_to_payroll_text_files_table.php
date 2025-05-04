<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddBankAccountFieldToPayrollTextFilesTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddBankAccountFieldToPayrollTextFilesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_text_files', function (Blueprint $table) {
            $table->string('bank_account_id')->nullable()->comment('Número de cuenta bancaria asociada al tipo de pago');
            $table->string('payment_type_id')->nullable()->comment('Identificador del tipo de pago asociado a la nómina');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_text_files', function (Blueprint $table) {
            $table->dropColumn('bank_account_id');
            $table->dropColumn('payment_type_id');
        });
    }
}
