<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class CreateBudgetFinancementSources
 *
 * @brief Gestión de las fuentes de financiamiento.
 *
 * Clase que gestiona los métodos para la gestión de las fuentes de financiamiento.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetFinancementSources extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_financement_sources')) {
            Schema::create('budget_financement_sources', function (Blueprint $table) {
                $table->id();
                $table->string('name', 200)->nullable()->comment('Nombre de la fuente de financiamiento');
                $table->foreignId('budget_financement_type_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
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
        DB::statement("DROP TABLE IF EXISTS budget_financement_sources CASCADE");
    }
}
