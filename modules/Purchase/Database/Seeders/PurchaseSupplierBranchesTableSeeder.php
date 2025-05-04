<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\PurchaseSupplierBranch;

/**
 * @class PurchaseSupplierBranchesTableSeeder
 * @brief Información por defecto para datos iniciales ramas de proveedores del módulo de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierBranchesTableSeeder extends Seeder
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
            $supplierBranches = [
                ['name' => 'Fabricante'],
                ['name' => 'Distribuidor'],
                ['name' => 'Distribuidor Autorizado'],
                ['name' => 'Obras'],
                ['name' => 'Servicios y/o Servicios Autorizados'],
            ];

            foreach ($supplierBranches as $supBr) {
                PurchaseSupplierBranch::updateOrCreate($supBr, $supBr);
            }
        });
    }
}
