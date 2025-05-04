<?php

namespace Modules\Budget\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\BudgetCompromise;
use App\Models\CodeSetting;
use App\Models\FiscalYear;

class BudgetUpdateCompromiseCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::transaction(function () {
            $year = date("Y");

            $codeSetting = CodeSetting::where("model", BudgetCompromise::class)->first();

            if (!$codeSetting) {
                return false;
            }

            $currentFiscalYear = FiscalYear::select('year')
                                        ->where(['active' => true, 'closed' => false])
                                        ->orderBy('year', 'desc')->first();

            $compromises = BudgetCompromise::withTrashed()
                                    ->where('code', 'not like', '%' . $currentFiscalYear->year . '%')
                                    ->orderBy('id', 'asc')
                                    ->get();

            foreach ($compromises as $compromise) {
                $code = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : $year),
                    BudgetCompromise::class,
                    'code'
                );

                $compromise->update(['code' => $code]);
            }
        });
    }
}
