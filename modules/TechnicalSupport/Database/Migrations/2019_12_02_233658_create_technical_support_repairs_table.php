<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateTechnicalSupportRepairsTable
 * @brief Crear tabla de reparaciones de bienes institucionales.
 *
 * Gestiona la creación o eliminación de la tabla de reparaciones de bienes institucionales.
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateTechnicalSupportRepairsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('technical_support_repairs')) {
            Schema::create('technical_support_repairs', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->string('state')->comment('Estado de la reparación');
                $table->text('result')->nullable()->comment('Descripción de los resultados de la reparación');
                $table->date('start_date')->nullable()->comment('Fecha de inicio de reparación');
                $table->date('end_date')->nullable()->comment('Fecha de culminación de reparación');

                $table->foreignId('user_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->unsignedBigInteger('technical_support_request_repair_id')->comment(
                    'Identificador único de la solcitud asociada a la reparación'
                );
                $table->foreign(
                    'technical_support_request_repair_id',
                    'technical_support_repairs_request_fk'
                )->references('id')->on(
                    'technical_support_request_repairs'
                )->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('technical_support_repairs');
    }
}
