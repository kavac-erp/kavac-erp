<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Models\PayrollBloodType;

/**
 * @class PayrollBloodTypesTableSeeder
 * @brief Carga de datos de tipos de sangre
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollBloodTypesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $bloodTypes = [
            'A positivo (A+)',
            'A negativo (A-)',
            'B positivo (B+)',
            'B negativo (B-)',
            'AB positivo (AB+)',
            'AB negativo (AB-)',
            'O positivo (O+)',
            'O negativo (O-)',
        ];

        DB::transaction(function () use ($bloodTypes) {
            foreach ($bloodTypes as $bloodType) {
                PayrollBloodType::updateOrCreate(['name' => $bloodType]);
            }
        });
    }
}
