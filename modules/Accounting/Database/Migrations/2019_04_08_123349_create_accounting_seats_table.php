<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateAccountingSeatsTable
 * @brief Ejecuta la migración de asientos contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAccountingSeatsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accounting_seats')) {
            Schema::create('accounting_seats', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('from_date')->nullable()->comment('Fecha del asiento contable');

                $table->text('concept')->nullable()->comment('Descripción del concepto del asiento contable');
                $table->text('observations')->nullable()->comment(
                    'Descripción de alguna observación para el asiento contable'
                );
                $table->text('reference')->comment(
                    'Referencia para identificar el asiento contable de forma directa (ej:SOP-11-2222)'
                );

                $table->float('tot_debit', 30, 2)->comment('Monto asignado al Debe total del asiento');
                $table->float('tot_assets', 30, 2)->comment('Monto asignado al Haber total del Asiento');

                $table->unsignedBigInteger('generated_by_id')->nullable()->comment(
                    'id de la categoria u origen por el cual se genero el asiento contable'
                );
                $table->foreign('generated_by_id')->references('id')->on('accounting_seat_categories')
                      ->onDelete('cascade')
                      ->comment('id de la categoria u origen por el cual se genero el asiento contable');


                $table->boolean('approved')->default(false)->comment('Indica si el asiento contable fue verificado');

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
        Schema::dropIfExists('accounting_seats');
    }
}
