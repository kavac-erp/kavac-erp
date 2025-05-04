<?php

/** [descripción del namespace] */

namespace Modules\Accounting\Database\Seeders;

use App\Models\DocumentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Models\AccountingEntry;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Accounting;

/**
 * @class $CLASS$
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingUpdateDocumentStatusIdAccountingEntriesTableSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de la base de datos
     *
     * @method run
     *
     * @return void     [descripción de los datos devueltos]
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
