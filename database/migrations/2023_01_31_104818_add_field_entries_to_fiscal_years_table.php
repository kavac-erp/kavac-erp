<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldEntriesToFiscalYearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('fiscal_years')) {
            if (!Schema::hasColumn('fiscal_years', 'entries')) {
                Schema::table('fiscal_years', function (Blueprint $table) {
                    $table->json('entries')->comment('Asientos contables de resultado de ejercicio')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('fiscal_years')) {
            if (Schema::hasColumn('fiscal_years', 'entries')) {
                Schema::table('fiscal_years', function (Blueprint $table) {
                    $table->dropColumn('entries');
                });
            }
        }
    }
}
