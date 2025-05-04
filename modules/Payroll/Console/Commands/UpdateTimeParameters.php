<?php

namespace Modules\Payroll\Console\Commands;

use App\Models\Parameter;
use Illuminate\Console\Command;

/**
 * @class UpdateTimeParameters
 * @brief Gestiona las instrucciones necesarias para actualizar los parámetros de tiempo
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateTimeParameters extends Command
{
    /**
     * El nombre y firma del comando, así como las opciones y argumentos que recibe
     *
     * @var string $signature
     */
    protected $signature = 'module:payroll-update-time-parameters';

    /**
     * Descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'update time parameters';

    /**
     * Crea una nueva instancia al comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta el comando de la consola.
     *
     * @return void
     */
    public function handle()
    {
        $parameters = Parameter::query()
            ->where(
                [
                    'active' => true,
                    'required_by' => 'payroll',
                ]
            )
            ->where('p_key', 'like', 'global_parameter_%')
            ->where('p_value', 'like', '%time_parameter%')
            ->get();

        foreach ($parameters as $parameter) {
            $data = json_decode($parameter->p_value, true);
            $data['list_in_schema'] = true;
            $newJson = json_encode($data);
            $parameter['p_value'] = $newJson;
            $parameter->save();
        }
    }
}
