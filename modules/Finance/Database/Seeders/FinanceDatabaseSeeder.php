<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class FinanceDatabaseSeeder
 * @brief Ejecuta los datos iniciales del módulo de finanzas
 *
 * Clase seeder del módulo de finanzas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(FinanceRoleAndPermissionsTableSeeder::class);

        $this->call(FinanceBanksTableSeeder::class);

        $this->call(FinanceAccountTypeTableSeeder::class);
    }
}
