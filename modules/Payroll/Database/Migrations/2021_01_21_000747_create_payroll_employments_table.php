<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollEmploymentsTable
 * @brief Crear tabla laboral del trabajador
 *
 * Gestiona la creación o eliminación de la tabla laboral del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollEmploymentsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_employments')) {
            Schema::create('payroll_employments', function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true)->comment('Indica si el trabajador está activo');
                $table->date('start_date_apn')->comment('Fecha de ingreso a la administración pública nacional');
                $table->date('start_date')->comment('Fecha de ingreso a la institución');
                $table->date('end_date')->nullable()->comment('Fecha de egreso de la institución');
                $table->string('institution_email', 100)
                      ->unique()->nullable()->comment('Correo electrónico institucional');
                $table->text('function_description')->nullable()->comment('Descripción de funciones');

                $table->foreignId('payroll_inactivity_type_id')->nullable()
                      ->comment('Identificador del tipo de inactividad')->constrained()
                      ->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_position_type_id')->comment('Identificador del tipo de cargo')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_position_id')->comment('Identificador del cargo')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_staff_type_id')->comment('Identificador del tipo de personal')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('department_id')->comment('Identificador del departamento')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_contract_type_id')->comment('Identificador del tipo de contrato')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

                $table->foreignId('payroll_staff_id')->unique()->comment('Identificador del dato personal')
                      ->constrained()->onUpdate('cascade')->onDelete('restrict');

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
        Schema::dropIfExists('payroll_employments');
    }
}
