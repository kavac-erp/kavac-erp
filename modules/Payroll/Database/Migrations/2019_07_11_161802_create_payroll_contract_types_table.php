<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollContractTypesTable
 * @brief Crear tabla de los tipos de contrato del trabajador
 *
 * Gestiona la creación o eliminación de la tabla de los tipos de contrato del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollContractTypesTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_contract_types')) {
            Schema::create('payroll_contract_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 100)->comment('Nombre del tipo de contrato');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_contract_types');
    }
}
