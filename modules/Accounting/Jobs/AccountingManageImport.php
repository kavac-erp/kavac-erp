<?php

namespace Modules\Accounting\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Accounting\Models\AccountingAccount;

/**
 * @class AccountingManageImport
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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
     * @var Array $data
     */
    protected $data;
    /**
     * Crea una nueva instancia de trabajo.
     *
     * @method __construct
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
     * @method handle
     *
     * @return void
     */
    public function handle()
    {
        $code = explode('.', $this->data['codigo']);

/** Información de cuenta padre */
        $parent = AccountingAccount::where('group', $code[0]);

        if ($parent) {
            for ($i = 1; $i <= 6; $i++) {
                if ($i == 1 && $code[$i] == 0) {
                    $parent->where('subgroup', '0');
                    break;
                } elseif ($i == 2 && $code[$i] == 0) {
                    $parent->where('subgroup', '0')
                        ->where('item', '0');
                    break;
                } elseif ($i == 3 && $code[$i] == 0) {
                    $parent->where('subgroup', $code[1])
                        ->where('item', '0')
                        ->where('generic', '00');
                    break;
                } elseif ($i == 4 && $code[$i] == 0) {
                    $parent->where('subgroup', $code[1])
                        ->where('item', $code[2])
                        ->where('generic', '00')
                        ->where('specific', '00');
                    break;
                } elseif ($i == 5 && $code[$i] == 0) {
                    $parent->where('subgroup', $code[1])
                        ->where('item', $code[2])
                        ->where('generic', $code[3])
                        ->where('specific', '00')
                        ->where('subspecific', '00');
                    break;
                } elseif ($i == 6 && $code[$i] == 0) {
                    $parent->where('subgroup', $code[1])
                        ->where('item', $code[2])
                        ->where('generic', $code[3])
                        ->where('specific', $code[4])
                        ->where('subspecific', '00')
                        ->where('institutional', '000');
                    break;
                }
            }
        }

        $parent_id = ($parent && $parent->first()) ? $parent->first()->id : null;

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
