<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * @class UpdateDataToPayrollConceptsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateDataToPayrollConceptsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::table('payroll_concepts')
            ->whereIn('payroll_concept_type_id', function ($query) {
                $query->select('id')
                      ->from('payroll_concept_types')
                      ->whereIn('sign', ['+', '-']);
            })
            ->update(['arc' => true]);
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
    }
}
