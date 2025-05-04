<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @class ParametersTableSeeder
 * @brief Información por defecto para parámetros del sistema
 *
 * Gestiona los parámetros por defecto a implementar en la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ParametersTableSeeder extends Seeder
{
    /**
     * Contador de parámetros cargados
     *
     * @var int $count
     */
    protected $count;

    /**
     * Método constructor de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        $this->count = 0;
    }

    /**
     * Ejecuta los seeers de base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $parameters = [
            ['p_key' => 'support', 'p_value' => 'false'],
            ['p_key' => 'chat', 'p_value' => 'false'],
            ['p_key' => 'notify', 'p_value' => 'false'],
            ['p_key' => 'report_banner', 'p_value' => 'false'],
            ['p_key' => 'multi_institution', 'p_value' => 'false'],
            ['p_key' => 'digital_sign', 'p_value' => 'false'],
            ['p_key' => 'online', 'p_value' => 'true'],
        ];

        $this->command->line("");
        $this->command->info("<fg=yellow>Cargando los Parámetros de Configuración</>");
        $this->command->line("");

        DB::transaction(function () use ($parameters) {
            foreach ($parameters as $parameter) {
                Parameter::withTrashed()->updateOrCreate(
                    ['p_key' => $parameter['p_key']],
                    [
                        'p_value' => $parameter['p_value'],
                        'required_by' => ($parameter['required_by']) ?? 'core',
                        'deleted_at' => null
                    ],
                );
                $this->count++;
            }
        });

        $this->command->info(
            "<fg=green>Se cargó y/o actualizó un total de</>" .
            "<fg=yellow> $this->count </><fg=green>Parámetros de Configuración</>"
        );
        $this->command->line("");
    }
}
