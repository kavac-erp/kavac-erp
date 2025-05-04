<?php

namespace Modules\Accounting\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Accounting\Models\AccountingAccount;

/**
 * @class AccountingManageImport
 * @brief Gestiona los procesos de importación de las cuentas contables
 *
 * Procesa las importaciones de cuentas contables
 *
 * @author Francisco Escala <fescala@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingManageImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Arreglo que contiene la información asociada a la solicitud
     *
     * @var array $data
     */
    protected $data;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return AccountingAccount|void
     */
    public function handle()
    {
        $mother = explode('.', $this->data['sub_especifica']);


        /* Información de cuenta padre */
        $parent = AccountingAccount::where('group', $mother[0]);

        if ($parent) {
                    $parent->where('subgroup', $mother[1])
                        ->where('item', $mother[2])
                        ->where('generic', $mother[3])
                        ->where('specific', $mother[4])
                        ->where('subspecific', $mother[5])
                        ->where('institutional', $mother[6]);
        }

        $parent_id = ($parent && $parent->first()) ? $parent->first()->id : null;

        $code = explode('.', $this->data['codigo']);
        if (count($code) == 7) {
            return AccountingAccount::updateOrCreate(
                [
                    'group' => $code[0],
                    'subgroup' => $code[1],
                    'item' => $code[2],
                    'generic' => $code[3],
                    'specific' => $code[4],
                    'subspecific' => $code[5],
                    'institutional' => $code[6],
                ],
                [
                    'parent_id' => $parent_id,
                    'denomination' => $this->data['denominacion'],
                    'resource' => isset($this->data['tipo_de_cuenta']) ? (($this->data['tipo_de_cuenta'] == 'INGRESO') ? true : false) : null,
                    'egress' => isset($this->data['tipo_de_cuenta']) ? (($this->data['tipo_de_cuenta'] == 'EGRESO') ? true : false) : null,
                    'active' => ($this->data['activa'] == 'SI') ? true : false,
                    'original' => ($this->data['original'] == 'SI') ? true : false,
                ]
            );
        }
    }
}
