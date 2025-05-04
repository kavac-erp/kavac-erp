<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class PurchaseDatabaseSeeder
 * @brief Información por defecto para datos iniciales del módulo de compra
 *
 * Gestiona la información por defecto a registrar inicialmente para los datos iniciales del módulo de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseDatabaseSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(PurchaseRoleAndPermissionsTableSeeder::class);
        $this->call(PurchaseSupplierObjectsTableSeeder::class);
        $this->call(PurchaseSupplierBranchesTableSeeder::class);
        $this->call(PurchaseSupplierSpecialtiesTableSeeder::class);
        $this->call(PurchaseSupplierTypesTableSeeder::class);
        $this->call(PurchaseProcessesTableSeeder::class);
        $this->call(PurchaseTypeOperationsTableSeeder::class);
    }
}
