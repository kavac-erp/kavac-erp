<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class    AddFieldToPayrollEmploymentsTable
 * 
 * @brief    Migración de la tabla payroll_employments
 *
 * Clase que gestiona la actualización de campos de la tabla payroll_employments.
 *
 * @author   Argenis Osorio <aosorio@cenditel.gob.ve>
 * 
 * @license  <a href='http://derechoinformatico.cenditel.gob.ve/licencia-de-software/'>
 *               LICENCIA DE SOFTWARE CENDITEL
 *           </a>
 */
class AddFieldPayrollCoordinationIdToEmploymentsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones, se agrega nuevo campos para 
     * la gestión de los datos de la tabla payroll_employments.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     * 
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_employments', 'payroll_coordination_id')) {
                $table->foreignId('payroll_coordination_id')
                    ->onUpdate('cascade')
                    ->onDelete('restrict')
                    ->nullable()
                    ->comment('Identificador de la coordinación');
            }
        });
    }

    /**
     * Método que elimina las migraciones.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     * 
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_employments', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_employments', 'payroll_coordination_id')) {
                $table->dropColumn('payroll_coordination_id');
            }
        });
    }
}
