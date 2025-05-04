<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Models\PurchaseTypeOperation;

/**
 * @class PurchaseTypeOperationsTableSeeder
 * @brief Información por defecto para datos iniciales de tipos de operación del módulo de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseTypeOperationsTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return void
     */
    public function run()
    {
        /*Model::unguard();

        DB::transaction(function () {
            $types = [
                ['name' => 'Bienes'],
                ['name' => 'Obras'],
                ['name' => 'Servicios'],
            ];

            foreach ($types as $type) {
                PurchaseTypeOperation::updateOrCreate($type, $type);
            }
        });*/
    }
}
