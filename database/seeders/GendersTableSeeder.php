<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Gender;

/**
 * @class GendersTableSeeder
 * @brief Inicializar los géneros
 *
 * Registra los géneros en base de datos
 *
 * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class GendersTableSeeder extends Seeder
{
    /**
     * Método que registra los valores de los géneros
     *
     * @author  Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javirrupe19@gmail.com>
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $genders = [
            [
                'name' => 'Masculino'
            ],
            [
                'name' => 'Femenino'
            ]
        ];

        DB::transaction(function () use ($genders) {
            foreach ($genders as $gender) {
                Gender::updateOrCreate(
                    ['name' => $gender['name']]
                );
            }
        });
    }
}
