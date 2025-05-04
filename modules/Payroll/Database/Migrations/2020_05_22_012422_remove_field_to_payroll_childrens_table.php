<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RemoveFieldToPayrollChildrensTable
 * @brief remueve el campo payroll_socioeconomic_information_id de la tabla
 *
 * Gestiona la creación o eliminación de la tabla
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveFieldToPayrollChildrensTable extends Migration
{
    /**
     * Método que elimina el campo payroll_socioeconomic_information_id de la tabla
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_childrens', function (Blueprint $table) {
            $foreignKeys = list_table_foreign_keys('payroll_childrens');
            if (in_array('payroll_childrens_payroll_socioeconomic_information_id_foreign', $foreignKeys)) {
                $table->dropForeign('payroll_childrens_payroll_socioeconomic_information_id_foreign');
            }
            if (Schema::hasColumn('payroll_childrens', 'payroll_socioeconomic_information_id')) {
                $table->dropColumn('payroll_socioeconomic_information_id');
            }
        });
    }

    /**
     * Método que agrega el campo payroll_socioeconomic_information_id a la tabla
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_childrens', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_childrens', 'payroll_socioeconomic_information_id')) {
                $table->foreignId('payroll_socioeconomic_information_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }
}
