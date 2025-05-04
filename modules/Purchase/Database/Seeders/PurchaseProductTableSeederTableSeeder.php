<?php

namespace Modules\Purchase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Purchase\Imports\PurchaseProductImport;

/**
 * @class PurchaseProductTableSeederTableSeeder
 * @brief Carga la información de la base de datos con los registros iniciales de productos del módulo de compras
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseProductTableSeederTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $file = base_path('modules/Purchase/Database/Seeders/data/catalogo_sistema_nacional_de_contrataciones.csv');

        $this->command->line("");
        $this->command->info(
            "<fg=yellow>Cargando información, este proceso puede demorar algunos minutos, por favor espere...</>"
        );
        $this->command->line("");

        Excel::import(new PurchaseProductImport(), $file);
    }
}
