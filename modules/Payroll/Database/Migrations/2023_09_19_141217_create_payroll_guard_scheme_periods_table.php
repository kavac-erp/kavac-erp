<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreatePayrollGuardSchemePeriodsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollGuardSchemePeriodsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_guard_scheme_periods', function (Blueprint $table) {
            $table->id();

            $table->date('from_date');
            $table->date('to_date');

            $table->foreignId('document_status_id')
                ->constrained('document_status')
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Estatus del documento asociado al periódo del esquema');

            $table->foreignId('payroll_guard_scheme_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Esquema de guardia asociado al periódo');

            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_guard_scheme_periods');
    }
}
