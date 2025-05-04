<?php

namespace Modules\Accounting\Console\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @class EntryDocumentStatusAprove
 * @brief Cambia el estado de un documento a aprobado que no fuere modificado en su estado de documento correctamente.
 *
 * Modifica el estatus de asientos contables
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class EntryDocumentStatusAprove extends Command
{
    /**
     * Nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:accounting-documentStatus-aprove';
    /**
     * Descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Cambia el estado de un documento a aprobado que no fuere modificado en su estado de documento correctamente.';

    /**
     * Crea una nueva instancia del comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecuta la consola de comandos.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                DB::table('accounting_entries')->where(['approved' => true, 'document_status_id' => 3])->update(['document_status_id' => 1]);
            });

            $this->info('Asientos contables actualizados correctamente.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
