<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateTechnicalSupportRepairDiagnosticsTable
 * @brief Crear tabla de diagnosticos de las reparaciones de bienes institucionales.
 *
 * Gestiona la creación o eliminación de la tabla de diagnosticos de las reparaciones de bienes institucionales.
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateTechnicalSupportRepairDiagnosticsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('technical_support_repair_diagnostics')) {
            Schema::create('technical_support_repair_diagnostics', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');
                $table->text('description')->nullable()->comment('Descripción del diagnóstico');

                $table->unsignedBigInteger('technical_support_repair_id')->comment(
                    'Identificador único de la reparación asociada al diagnóstico'
                );
                $table->foreign(
                    'technical_support_repair_id',
                    'technical_support_repair_diagnostics_repair_fk'
                )->references('id')->on('technical_support_repairs')->onDelete('restrict')->onUpdate('cascade');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        };
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('technical_support_repair_diagnostics');
    }
}
