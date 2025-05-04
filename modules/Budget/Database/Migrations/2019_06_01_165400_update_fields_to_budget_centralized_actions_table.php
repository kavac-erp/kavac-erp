<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldsToBudgetCentralizedActionsTable
 * @brief Actualiza los tipos de dato de los campos de la tabla budget_centralized_actions
 *
 * Gestiona la creación o eliminación de la tabla de cuentas presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldsToBudgetCentralizedActionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_centralized_actions', function (Blueprint $table) {
            if (Schema::hasColumn('budget_centralized_actions', 'payroll_position_id')) {
                $table->bigInteger('payroll_position_id')->unsigned()->nullable()
                      ->comment(
                          'Identificador asociado al cargo de la persona responsable del proyecto'
                      )->change();
            }
            if (Schema::hasColumn('budget_centralized_actions', 'payroll_staff_id')) {
                $table->bigInteger('payroll_staff_id')->unsigned()->nullable()
                      ->comment(
                          'Identificador asociado al cargo de la persona responsable del proyecto'
                      )->change();
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_centralized_actions', function (Blueprint $table) {
            if (Schema::hasColumn('budget_centralized_actions', 'payroll_position_id')) {
                $table->bigInteger('payroll_position_id')->unsigned()
                      ->comment(
                          'Identificador asociado al cargo de la persona responsable del proyecto'
                      )->change();
            }
            if (Schema::hasColumn('budget_centralized_actions', 'payroll_staff_id')) {
                $table->bigInteger('payroll_staff_id')->unsigned()
                      ->comment(
                          'Identificador asociado al cargo de la persona responsable del proyecto'
                      )->change();
            }
        });
    }
}
