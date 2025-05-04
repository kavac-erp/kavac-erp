<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Deduction
 * @brief Datos de Deducciones
 *
 * Gestiona el modelo de datos para las Deducciones
 *
 * @property  string|integer  $id
 * @property  string  $name
 * @property  string  $description
 * @property  string  $formula
 * @property  boolean $active
 * @property  integer $accounting_account_id
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Deduction extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['name', 'description', 'formula', 'active', 'accounting_account_id'];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Metodo que obtiene la Cuenta Contable de una deducción
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (Module::has('Accounting') && Module::isEnabled('Accounting'))
                ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : [];
    }
}
