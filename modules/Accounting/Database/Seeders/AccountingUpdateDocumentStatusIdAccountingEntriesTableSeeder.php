<?php

namespace Modules\Accounting\Database\Seeders;

use App\Models\DocumentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Models\AccountingEntry;

/**
 * @class AccountingUpdateDocumentStatusIdAccountingEntriesTableSeeder
 * @brief Actualiza el estatus de los asientos contables
 *
 * Actualiza el estatus de los asientos contables
 *
 * @author Ing. Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingUpdateDocumentStatusIdAccountingEntriesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $AccountingEntries = AccountingEntry::all();
        $documentStatusAP = DocumentStatus::where('action', 'AP')->first();
        if (isset($AccountingEntries)) {
            foreach ($AccountingEntries as $AccountingEntry) {
                if ($AccountingEntry->approved == true) {
                    $AccountingEntry['document_status_id'] = $documentStatusAP->id;
                    $AccountingEntry->save();
                }
            }
        }
    }
}
