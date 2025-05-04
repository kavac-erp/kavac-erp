<?php

namespace Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\PayrollRelationship;

/**
 * @class PayrollRelationshipsTableSeeder
 * @brief Inserta o actualiza la base de datos con datos de los tipos de parentescos
 *
 * Esta clase se encarga de insertar o actualizar en la base de datos los tipos de parentescos.
 * Obtiene los datos de un archivo de recursos JSON
 *
 * @author Manuel Zambrano <mazambrano@centidel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollRelationshipsTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run(): void
    {
         // Desactivar la protección de asignación masiva
        Model::unguard();

        try {
            /* Obtiene los datos a cargar de un recurso json
            modules/Payroll/Resources/Data/PayrollRelationship.json */
            $payrollRelationships = get_json_resource('Data/PayrollRelationship.json', 'payroll');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            error_log($th->getMessage());
            throw $th;
        }

        try {
            collect($payrollRelationships)->each(function ($payrollRelationship) {
                PayrollRelationship::updateOrCreate(
                    ['name' => $payrollRelationship->name],
                    ['description' => $payrollRelationship->description]
                );
            });
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            error_log($e->getMessage());
            throw $e;
        }
    }
}
