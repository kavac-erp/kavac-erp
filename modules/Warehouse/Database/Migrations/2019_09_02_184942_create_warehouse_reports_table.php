<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateWarehouseReportsTable
 * @brief Crear tabla de los reportes generados en el módulo de almacén
 *
 * Gestiona la creación o eliminación de la tabla de reportes generados en el módulo de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateWarehouseReportsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('warehouse_reports')) {
            Schema::create('warehouse_reports', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Identificador único del registro');

                $table->string('code', 20)->unique()->comment('Código identificador del reporte');

                $table->string('type_report', 20)->nullable()->comment('Tipo de reporte');

                $table->foreignId('warehouse_product_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')
                      ->comment('Identificador único del producto almacenable asociado al reporte');
                $table->foreignId('warehouse_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')
                      ->comment('Identificador único del almacén asociado al reporte');
                $table->foreignId('institution_id')->nullable()->constrained()
                      ->onDelete('restrict')->onUpdate('cascade')
                      ->comment('Identificador único de la institución asociada al reporte');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_reports');
    }
}
