<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class AddFieldsToPayrollChildrensTable
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [José Briceño] [josejorgebriceno9@gmail.com]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldsToPayrollChildrensTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payroll_childrens')) {
            Schema::table('payroll_childrens', function (Blueprint $table) {
                if (!Schema::hasColumn('payroll_childrens', 'payroll_schooling_level_id')) {
                    $table->foreignId('payroll_schooling_level_id')->nullable()
                        ->comment('Identificador del nivel de escolaridad')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
                }

                if (!Schema::hasColumn('payroll_childrens', 'payroll_disability_id')) {
                    $table->foreignId('payroll_disability_id')->nullable()
                        ->comment('Identificador de la discapacidad')->constrained()
                        ->onUpdate('cascade')->onDelete('restrict');
                }

                if (!Schema::hasColumn('payroll_childrens', 'is_student')) {
                    $table->boolean('is_student')->default(false)->comment('Indica si el hijo del trabajador es estudiante');
                }

                if (!Schema::hasColumn('payroll_childrens', 'has_disability')) {
                    $table->boolean('has_disability')->default(false)->comment('Indica si el hijo del trabajador tiene una discapacidad');
                }

                if (!Schema::hasColumn('payroll_childrens', 'study_center')) {
                    $table->string('study_center', 100)->nullable()->comment('Centro de estudio del hijo del trabajador');
                }
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
        if (Schema::hasTable('payroll_childrens')) {
            Schema::table('payroll_childrens', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_childrens', 'payroll_schooling_level_id')) {
                    $table->dropForeign(['payroll_schooling_level_id']);
                    $table->dropColumn('payroll_schooling_level_id');
                }

                if (Schema::hasColumn('payroll_childrens', 'payroll_disability_id')) {
                    $table->dropForeign(['payroll_disability_id']);
                    $table->dropColumn('payroll_disability_id');
                }

                if (Schema::hasColumn('payroll_childrens', 'is_student')) {
                    $table->dropColumn('is_student');
                }

                if (Schema::hasColumn('payroll_childrens', 'has_disability')) {
                    $table->dropColumn('has_disability');
                }

                if (Schema::hasColumn('payroll_childrens', 'study_center')) {
                    $table->dropColumn('study_center');
                }
            });
        }
    }
}
