<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class    AddFieldToPayrollPositionsTable
 * 
 * @brief    Migración de la tabla payroll_positions
 *
 * Clase que gestiona la actualización de campos de la tabla payroll_positions.
 *
 * @author   Argenis Osorio <aosorio@cenditel.gob.ve>
 * 
 * @license  <a href='http://derechoinformatico.cenditel.gob.ve/licencia-de-software/'>
 *               LICENCIA DE SOFTWARE CENDITEL
 *           </a>
 */
class AddFieldToPayrollPositionsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones, se agrega nuevo campos para 
     * la gestión de los datos de la tabla payroll_positions.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     * 
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_positions', function (Blueprint $table) {
            $table->integer('number_positions_assigned')
                ->nullable()
                ->comment('Cantidad de cargos asignados');
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
        Schema::table('payroll_positions', function (Blueprint $table) {
            $table->dropColumn('number_positions_assigned');
        });
    }
}
