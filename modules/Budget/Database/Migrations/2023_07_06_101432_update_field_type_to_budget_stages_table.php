<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class UpdateFieldTypeToBudgetStagesTable
 * @brief Actualiza el tipo de dato del campo 'type2' de la tabla 'budget_stages'
 *
 * @author Francisco J. P. Ruiz <fpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldTypeToBudgetStagesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('budget_stages')) {
            Schema::table('budget_stages', function (Blueprint $table) {
                if (Schema::hasColumn('budget_stages', 'type2')) {
                    $table->dropColumn(['type2']);
                }
            });

            Schema::table('budget_stages', function ($table) {
                $table->enum('type2', ['PRE', 'PRO', 'COM', 'CAU', 'PAG', 'ANU'])->nullable();
            });
            DB::statement('UPDATE "budget_stages" SET type2 = type ');
            Schema::table('budget_stages', function (Blueprint $table) {
                if (Schema::hasColumn('budget_stages', 'type')) {
                    $table->dropColumn(['type']);
                }
            });

            Schema::table('budget_stages', function (Blueprint $table) {
                if (!Schema::hasColumn('budget_stages', 'type')) {
                    $table->string('type', 3)->nullable()
                    ->comment(
                        'Identifica las etapas presupuestarias del compromiso. Ej.
                        (PRE)compromiso,
                        (PRO)gramado,
                        (COM)prometido,
                        (CAU)sado,
                        (PAG)ado,
                        (ANU)lado'
                    );
                }
            });
            DB::statement('UPDATE "budget_stages" SET type = type2');
            Schema::table('budget_stages', function (Blueprint $table) {
                $table->dropColumn(['type2']);
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
        if (Schema::hasTable('budget_stages')) {
            Schema::table('budget_stages', function (Blueprint $table) {
                if (Schema::hasColumn('budget_stages', 'type')) {
                    $table->dropColumn(['type']);
                }
            });
            Schema::table('budget_stages', function (Blueprint $table) {
                if (!Schema::hasColumn('budget_stages', 'type')) {
                    $table->enum('type', ['PRE', 'PRO', 'COM', 'CAU', 'PAG'])
                    ->default('PRE')
                    ->comment(
                        'Identifica las etapas presupuestarias del compromiso. Ej.
                        (PRE)compromiso,
                        (PRO)gramado,
                        (COM)prometido,
                        (CAU)sado, (PAG)ado'
                    );
                }
            });
        }
    }
}
