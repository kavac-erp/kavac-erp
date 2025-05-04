<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class    AddNewFieldToPayrollPositionsTable
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
class AddNewFieldToPayrollPositionsTable extends Migration
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
        if (Schema::hasTable('payroll_positions')) {
            Schema::table('payroll_positions', function (Blueprint $table) {
                $table->boolean('responsible')
                    ->nullable()
                    ->default(false)
                    ->comment('Cargo de tipo responsable');
            });
        }
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
        // Eliminar el campo responsible de la tabla payroll_positions.
        if (Schema::hasTable('payroll_positions')) {
            Schema::table('payroll_positions', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_positions', 'responsible')) {
                    $table->dropColumn('responsible');
                }
            });
        }
    }
}
