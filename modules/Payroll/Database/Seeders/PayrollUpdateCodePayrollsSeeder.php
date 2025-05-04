<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\Payroll;
use App\Models\CodeSetting;

/**
 * @class PayrollUpdateCodePayrollsSeeder
 * @brief Actualiza los códigos de nómia
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollUpdateCodePayrollsSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
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
