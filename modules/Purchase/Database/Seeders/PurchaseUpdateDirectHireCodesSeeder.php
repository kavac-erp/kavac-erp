<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Models\PurchaseDirectHire;
use App\Models\CodeSetting;
use App\Models\FiscalYear;

/**
 * @class PurchaseUpdateDirectHireCodesSeeder
 * @brief Información por defecto para la actualización de códigos de contrataciones directas del módulo de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseUpdateDirectHireCodesSeeder extends Seeder
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
            $year = date("Y");

            $codeSetting = CodeSetting::where("model", PurchaseDirectHire::class)->first();

            if (!$codeSetting) {
                return false;
            }

            $currentFiscalYear = FiscalYear::select('year')
                                        ->where(['active' => true, 'closed' => false])
                                        ->orderBy('year', 'desc')->first();

            $directHires = PurchaseDirectHire::withTrashed()
                                ->orderBy('id', 'asc');
            $directHires->update(['code' => null]);
            $directHires = $directHires->get();

            foreach ($directHires as $directHire) {
                $codeDirectHire = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : $year),
                    PurchaseDirectHire::class,
                    'code'
                );

                $directHire->update(['code' => $codeDirectHire]);
            }
        });
    }
}
