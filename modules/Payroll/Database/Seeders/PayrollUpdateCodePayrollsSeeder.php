<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Payroll\Models\Payroll;
use App\Models\CodeSetting;

class PayrollUpdateCodePayrollsSeeder extends Seeder
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
            $codeSetting = CodeSetting::where(['model' => Payroll::class, 'table' => 'payrolls'])->first();

            if (!$codeSetting) {
                return false;
            }

            $payrolls = Payroll::withTrashed()
                                ->orderBy('id', 'asc');
            $payrolls->update(['code' => null]);
            $payrolls = $payrolls->get();

            foreach ($payrolls as $payroll) {
                list($year, $month, $day) = explode("-", $payroll->created_at);

                $code = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (strlen($codeSetting->format_year) == 2) ? substr($year, 0, 2) : $year,
                    Payroll::class,
                    'code'
                );

                $payroll->update(['code' => $code]);
            }
        });
    }
}
