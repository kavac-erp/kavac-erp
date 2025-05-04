<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Models\FinanceAccountType;

/**
 * @class FinanceAccountTypeTableSeeder
 * @brief Carga de datos en la tabla de finance_account_types
 *
 * Clase seeder de la tabla de finance_account_types
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceAccountTypeTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        try {
            /* Obtiene los datos a cargar de un recurso json
            modules/Finance/Resources/Data/FinanceDataType.json */
            $financeAccountTypes = get_json_resource('Data/FinanceAccountType.json', 'finance');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            error_log($th->getMessage());
            throw $th;
        }

        try {
            collect($financeAccountTypes)->each(function ($financeAccountType) {
                financeAccountType::updateOrCreate(
                    ['name' => $financeAccountType->name],
                    ['code' => $financeAccountType->code]
                );
            });
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            error_log($e->getMessage());
            throw $e;
        }
    }
}
