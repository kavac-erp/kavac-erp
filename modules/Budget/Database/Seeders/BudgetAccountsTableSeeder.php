<?php

namespace Modules\Budget\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Budget\Models\BudgetAccount;

/**
 * @class BudgetAccountsTableSeeder
 * @brief Información por defecto para cuentas presupuestarias
 *
 * Gestiona la información por defecto a registrar inicialmente para las cuentas presupuestarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetAccountsTableSeeder extends Seeder
{
    /**
     * Método que ejecuta el seeder e inserta los datos en la base de datos.
     *
     * @return boolean|void
     */
    public function run()
    {
        Model::unguard();

        $filename = base_path("modules/Budget/Database/Seeders/CSV/clasificador-presupuestario.csv");

        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando Clasificador presupuestario</>");
        $this->command->line("");

        $csvFile = fopen(base_path("modules/Budget/Database/Seeders/CSV/clasificador-presupuestario.csv"), "r");

        DB::transaction(function () use ($csvFile) {
            $count = 0;
            $firstline = true;
            while (($data = fgetcsv($csvFile, 2000, ";")) !== false) {
                if (!$firstline) {
                    list($group, $item, $generic, $specific, $subspecific) = explode(".", $data[0]);
                    $denomination = $data[1];
                    $parent = BudgetAccount::getParent($group, $item, $generic, $specific, $subspecific);

                    BudgetAccount::updateOrCreate(
                        [
                            'group' => $group, 'item' => $item, 'generic' => $generic,
                            'specific' => $specific, 'subspecific' => $subspecific
                        ],
                        [
                            'denomination' => trim($denomination), 'active' => true,
                            'inactivity_date' => null,
                            'resource' => ((int)$group === 3),
                            'egress' => ((int)$group === 4),
                            'parent_id' => ($parent == false) ? null : $parent->id
                        ]
                    );
                    $count++;
                }

                $firstline = false;
            }
            $this->command->line("");
            $this->command->info("<fg=green>Se cargo un total de</><fg=yellow> $count </><fg=green>cuentas presupuestarias</>");
            $this->command->line("");
        });

        fclose($csvFile);
    }
}
