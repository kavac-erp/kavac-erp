<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldCodeToSaleBillsTable
 * @brief Agregar el campo code a las facturas
 *
 * Gestiona la creación o eliminación de un campo de la tabla facturas
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class AddFieldCodeToSaleBillsTable extends Migration
{
    /**
     * Método que ejecuta las migraciones
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_bills', function (Blueprint $table) {
            $table->string('code', 20)->unique()->comment('Código identificador de la solicitud');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_bills', function (Blueprint $table) {
            if (Schema::hasColumn('sale_bills', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
        });
    }
}
