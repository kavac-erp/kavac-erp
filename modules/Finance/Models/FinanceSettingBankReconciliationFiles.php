<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class FinanceSettingBankReconciliationFiles
 *
 * @brief Configuraciones de los archivos de conciliación bancaria.
 *
 * Gestiona el modelo de datos para las configuraciones de los archivos de
 * conciliación bancaria.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceSettingBankReconciliationFiles extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = 'finance_setting_bank_reconciliation_files';
    public $timestamps = true;

    protected $fillable = [
        'bank_id',
        'read_start_line',
        'read_end_line',
        'balance_according_bank',
        'position_reference_column',
        'position_date_column',
        'position_debit_amount_column',
        'position_credit_amount_column',
        'position_description_column',
        'position_balance_according_bank',
        'separated_by',
        'date_format',
        'thousands_separator',
        'decimal_separator'
    ];
}
