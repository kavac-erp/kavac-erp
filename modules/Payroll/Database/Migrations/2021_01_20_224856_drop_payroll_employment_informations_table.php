<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class DropPayrollEmploymentInformationsTable
 * @brief Elimina la tabla payroll_employment_informations
 *
 * Gestiona la creación o eliminación de la tabla
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DropPayrollEmploymentInformationsTable extends Migration
{
    /**
     * Método que elimina la tabla payroll_employment_informations
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('payroll_employment_informations');
    }

    /**
     * Método que crea la tabla payroll_employment_informations
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('payroll_employment_informations')) {
            Schema::create('payroll_employment_informations', function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true)->comment('Indica si el trabajador está activo');
                $table->date('start_date_apn')->comment('Fecha de ingreso a la administración pública nacional');
                $table->date('start_date')->comment('Fecha de ingreso a la institución');
                $table->date('end_date')->nullable()->comment('Fecha de egreso de la institución');
                $table->string('institution_email', 100)
                      ->unique()->nullable()->comment('Correo electrónico institucional');
                $table->text('function_description')->nullable()->comment('Descripción de funciones');

                $table->unsignedBigInteger('payroll_inactivity_type_id')->nullable()
                      ->comment('identificador del tipo de inactividad que pertenece a la información laboral');
                $table->foreign(
                    'payroll_inactivity_type_id',
                    'payroll_employment_informations_inactivity_type_fk'
                )->references('id')->on('payroll_inactivity_types')->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('payroll_position_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('payroll_position_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('payroll_staff_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('department_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('payroll_contract_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->foreignId('payroll_staff_id')->unique()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }
}
