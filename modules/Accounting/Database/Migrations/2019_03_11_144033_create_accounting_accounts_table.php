<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingAccountsTable
 * @brief Ejecuta la migración de cuentas contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingAccountsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accounting_accounts')) {
            Schema::create('accounting_accounts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('group', 1)->comment('Grupo al que pertenece la cuenta');
                $table->char('subgroup', 1)->comment('SubGrupo al que pertenece la cuenta');
                $table->char('item', 1)->comment('Rubro al que pertenece la cuenta');
                $table->char('generic', 2)->comment('Numero de cuenta al que pertenece');
                $table->char('specific', 2)->comment('Numero de subcuenta de primer orden');
                $table->char('subspecific', 2)->comment('Numero de subcuenta de segundo orden');
                $table->text('denomination')->comment('Descripción de la cuenta');

                $table->boolean('active')->default(true)->comment('Indica si la cuenta esta activa');
                $table->date('inactivity_date')->nullable()->comment('Fecha en la que se inactiva la cuenta');


                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
                $table->timestamps();
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
        Schema::dropIfExists('accounting_accounts');
    }
}
