<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Institution;
use App\Models\FiscalYear;

class ChangeUniqueRestrictionsToFiscalYearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $institution = Institution::where('default', true)->first();
        if ($institution) {
            /** @var Object Si existen años fiscales no asociados a organismos, se asigna el organismo por defecto */
            $fiscalYears = FiscalYear::whereNull('institution_id')->get();
            foreach ($fiscalYears as $fiscalYear) {
                $fiscalYear->institution_id = $institution->id;
                $fiscalYear->save();
            }
        }

        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->dropUnique(['year']);
            $table->bigInteger('institution_id')->change();
        });
        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->unique(['year', 'institution_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->dropUnique(['year', 'institution_id', 'active']);
        });
        Schema::table('fiscal_years', function (Blueprint $table) {
            $table->string('year', 4)->unique()->comment('Año fiscal')->change();
            $table->bigInteger('institution_id')->nullable()->change();
        });
    }
}
