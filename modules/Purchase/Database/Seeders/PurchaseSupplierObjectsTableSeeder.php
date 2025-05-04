<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\PurchaseSupplierObject;

/**
 * @class PurchaseSupplierObjectsTableSeeder
 * @brief Información por defecto para datos iniciales de objetos de proveedores del módulo de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierObjectsTableSeeder extends Seeder
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
            $supplierObjects = [
                ['type' => 'B', 'name' => 'Productos'],
                ['type' => 'B', 'name' => 'Materiales'],
                ['type' => 'B', 'name' => 'Maquinarias'],
                ['type' => 'B', 'name' => 'Equipos'],
                ['type' => 'B', 'name' => 'Muebles'],
                ['type' => 'O', 'name' => 'Mano de Obra'],
                ['type' => 'S', 'name' => 'Mano de Obra'],
            ];

            foreach ($supplierObjects as $supObj) {
                PurchaseSupplierObject::updateOrCreate($supObj, $supObj);
            }
        });
    }
}
