<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class    AddFieldToPayrollResponsibilities
 * 
 * @brief    Migración de la tabla payroll_responsibilities
 *
 * Clase que gestiona la actualización de campos de la tabla payroll_responsibilities.
 *
 * @author   Argenis Osorio <aosorio@cenditel.gob.ve>
 * 
 * @license  <a href='http://derechoinformatico.cenditel.gob.ve/licencia-de-software/'>
 *               LICENCIA DE SOFTWARE CENDITEL
 *           </a>
 */
class AddFieldToPayrollResponsibilities extends Migration
{
    /**
     * Método que ejecuta las migraciones, se agrega nuevo campo para 
     * la gestión de los datos de la tabla payroll_responsibilities.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     * 
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_responsibilities')) {
            Schema::table('payroll_responsibilities', function (Blueprint $table) {
                $table->boolean('type_responsibility')
                    ->nullable()
                    ->default(false)
                    ->comment('false Departamento true Coordinación');
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
        // Eliminar el campo payroll_responsibilities de la tabla payroll_employments.
        if (Schema::hasTable('payroll_responsibilities')) {
            Schema::table('payroll_responsibilities', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_responsibilities', 'type_responsibility')) {
                    $table->dropColumn('type_responsibility');
                }
            });
        }
    }
}
