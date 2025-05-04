<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetFinancementTypes
 *
 * @brief Gestión de los tipos de financiamiento.
 *
 * Clase que gestiona los métodos para la gestión de los tipos de
 * financiamiento.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetFinancementTypes extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_financement_types')) {
            Schema::create('budget_financement_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 200)->nullable()->comment('Nombre de la fuente de financiamiento');
                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP TABLE IF EXISTS budget_financement_types CASCADE");
    }
}
