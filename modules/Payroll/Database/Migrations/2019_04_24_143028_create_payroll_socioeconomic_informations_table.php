<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollSocioeconomicInformationsTable
 * @brief Crear tabla de la información socioeconómica del trabajador
 *
 * Gestiona la creación o eliminación de la tabla de información socioeconómica del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollSocioeconomicInformationsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_socioeconomic_informations')) {
            Schema::create('payroll_socioeconomic_informations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('full_name_twosome', 200)->nullable()
                      ->comment('Nombres y apellidos de la pareja del trabajador');
                $table->string('id_number_twosome', 12)->nullable()->comment('cédula de la pareja del trabajador');
                $table->date('birthdate_twosome')->nullable()
                      ->comment('Fecha de nacimiento de la pareja del trabajador');

                $table->foreignId('payroll_staff_id')->unique()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('marital_status_id')->constrained('marital_status')
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
        Schema::dropIfExists('payroll_socioeconomic_informations');
    }
}
