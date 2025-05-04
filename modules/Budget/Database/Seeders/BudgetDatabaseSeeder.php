<?php

namespace Modules\Budget\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class BudgetDatabaseSeeder
 * @brief Información por defecto para datos iniciales del módulo de presupuesto
 *
 * Gestiona la información por defecto a registrar inicialmente para los datos iniciales del módulo de presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        /* Seeder para clasificador presupuestario original */
        $this->call(BudgetAccountsTableSeeder::class);
        /* Seeder para roles y permisos disponibles en el módulo */
        $this->call(BudgetRoleAndPermissionsTableSeeder::class);
        /* Seeder que carga los datos de los tipos y fuentes de financiamiento */
        // $this->call(BudgetFinancementTypesAndSourcesTableSeeder::class);
    }
}
