<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldGenderIdToPayrollStaffsTable
 * @brief Crear el campo gender_id a la información personal del trabajador
 *
 * Gestiona la creación o eliminación de un campo de la tabla información personal del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldGenderIdToPayrollStaffsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_staffs', 'payroll_gender_id')) {
                $table->unsignedBigInteger('payroll_gender_id');
                $table->foreign("payroll_gender_id")
                        ->references("id")
                        ->on("genders")
                        ->onDelete("restrict")
                        ->onUpdate("cascade");
            }
        });
    }

    /**
     * Método que elimina las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_staffs', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_staffs', 'payroll_gender_id')) {
                DB::statement("ALTER TABLE payroll_staffs DROP CONSTRAINT IF EXISTS payroll_staffs_payroll_gender_id_foreign");
                $table->dropColumn('payroll_gender_id');
            }
        });
    }
}
