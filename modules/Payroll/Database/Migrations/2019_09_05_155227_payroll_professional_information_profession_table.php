<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollProfessionalInformationProfessionTable
 * @brief Crear tabla intermedia entre la información profesional y la profesión
 *
 * Gestiona la creación o eliminación de la tabla intermedia entre información profesional y profesión
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollProfessionalInformationProfessionTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payroll_professional_information_profession')) {
            Schema::create('payroll_professional_information_profession', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->unsignedBigInteger('payroll_professional_information_id');
                $table->foreign(
                    'payroll_professional_information_id',
                    'payroll_professional_information_profession_professional_fk'
                )->references('id')->on('payroll_professional_informations')->onDelete('cascade');

                $table->unsignedBigInteger('profession_id');
                $table->foreign(
                    'profession_id',
                    'payroll_professional_information_profession_fk'
                )->references('id')->on('professions')->onDelete('cascade');

                $table->timestamps();
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
        Schema::drop('payroll_professional_information_profession');
    }
}
