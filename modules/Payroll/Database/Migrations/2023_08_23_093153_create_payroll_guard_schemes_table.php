<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @class CreatePayrollGuardSchemesTable
 *
 * @brief Crear tabla de esquemas de guardias
 *
 * Gestiona la creación o eliminación de la tabla de esquemas de guardias
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreatePayrollGuardSchemesTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @return    void
     */
    public function up()
    {
        Schema::create('payroll_guard_schemes', function (Blueprint $table) {
            $table->id();
            $table->date('from_date');
            $table->date('to_date');
            $table->foreignId('payroll_supervised_group_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Grupo de supervisados asociado al esquema');

            $table->foreignId('institution_id')
                ->constrained()
                ->onDelete('restrict')
                ->onUpdate('cascade')
                ->comment('Institución asociada al esquema');

            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });
    }

    /**
     * Método que revierte las migraciones.
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @return    void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_guard_schemes');
    }
}
