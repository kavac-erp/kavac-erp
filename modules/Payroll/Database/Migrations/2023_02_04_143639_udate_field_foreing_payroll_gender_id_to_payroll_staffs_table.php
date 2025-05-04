<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * @class UdateFieldForeingPayrollGenderIdToPayrollStaffsTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UdateFieldForeingPayrollGenderIdToPayrollStaffsTable extends Migration
{
    /**
    * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_staffs', function (Blueprint $table) {
            $table->dropForeign('payroll_staffs_payroll_gender_id_foreign');

            
            $table->foreign("payroll_gender_id")
                        ->references("id")
                        ->on("genders")
                        ->onDelete("restrict")
                        ->onUpdate("cascade");
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
