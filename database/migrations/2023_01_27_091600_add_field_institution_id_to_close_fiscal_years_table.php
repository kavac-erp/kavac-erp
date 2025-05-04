<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldInstitutionIdToCloseFiscalYearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('close_fiscal_years')) {
            Schema::table('close_fiscal_years', function (Blueprint $table) {
                $table->foreignId('institution_id')->constrained()
                      ->onUpdate('restrict')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('close_fiscal_years')) {
            if (Schema::hasColumn('close_fiscal_years', 'institution_id')) {
                Schema::table('close_fiscal_years', function (Blueprint $table) {
                    $table->dropForeign('close_fiscal_years_institution_id_foreign');
                    $table->dropColumn('institution_id');
                });
            }
        }
    }
}
