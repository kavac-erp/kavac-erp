<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollChildrensTable
 * @brief Crear tabla de hijos de los trabajadores
 *
 * Gestiona la creación o eliminación de la tabla de hijos de los trabajadores
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollChildrensTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_childrens')) {
            Schema::create('payroll_childrens', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('first_name', 100)->comment('Nombre del hijo del trabajador');
                $table->string('last_name', 100)->comment('Apellido del hijo del trabajador');
                $table->string('id_number', 12)->nullable()->comment('Cédula del hijo del trabajador');
                $table->date('birthdate')->comment('Fecha de nacimiento del hijo del trabajador');

                $table->foreignId('payroll_socioeconomic_information_id')->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('payroll_childrens');
    }
}
