<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollStaffAccountsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollStaffAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_staff_accounts', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('payroll_staff_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Trabajador asociado a la cuenta contable');
            $table
                ->foreignId('accounting_account_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Cuenta contable asociada al trabajador');
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
        Schema::dropIfExists('payroll_staff_accounts');
    }
}
