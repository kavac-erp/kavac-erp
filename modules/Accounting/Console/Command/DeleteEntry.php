<?php

namespace Modules\Accounting\Console\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Models\AccountingEntry;

/**
 * @class DeleteEntry
 * @brief Elimina un asiento contable y sus relaciones.
 *
 * Elimina asientos contables
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DeleteEntry extends Command
{
    /**
     * Nombre del comando.
     *
     * @var string $signature
     */
    protected $signature = 'module:accounting-delete-entry
                            {entry_id : El ID del asiento.}';

    /**
     * Descripción del comando.
     *
     * @var string $description
     */
    protected $description = 'Elimina el asiento y sus relaciones.';

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
            $entryId = $this->argument('entry_id');
            DB::transaction(function () use ($entryId) {
                $entry = AccountingEntry::findOrFail($entryId);
                // Elimina las relaciones de AccountingEntryAccount
                DB::table('accounting_entry_accounts')->where(['accounting_entry_id' => $entryId])->delete();
                DB::table('accounting_entryables')->where(['accounting_entry_id' => $entryId])->delete();
                /* Elimina la relación entre el asiento contable y el registro de orden de pago o movimiento*/
                $entry->delete();
            });

            $this->info('delete accounting accounts created successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
