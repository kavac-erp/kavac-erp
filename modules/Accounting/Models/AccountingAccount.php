<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AccountingAccount
 * @brief Datos de cuentas del Clasificador Patrimoniales
 *
 * Gestiona el modelo de datos para las cuentas del Clasificador Patrimoniales
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingAccount extends Model implements Auditable
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
     * Listado de campos adjuntos a los campos por defecto
     *
     * @var    array $append
     */
    protected $appends = ['code'];

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'group',
        'subgroup',
        'item',
        'generic',
        'specific',
        'subspecific',
        'institutional',
        'denomination',
        'active',
        'resource',
        'egress',
        'inactivity_date',
        'parent_id',
        'original'
    ];

    /**
     * Establece el tipo de relación con la cuenta contable
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function accountable()
    {
        return $this->morphMany(Accountable::class, 'accountable');
    }

    /**
     * Establece la relación con el asiento contable
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entryAccount()
    {
        return $this->hasMany(AccountingEntryAccount::class);
    }

    /**
     * Método que permite obtener la cuenta asociada de nivel superior
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  string $group       Grupo de la cuenta
     * @param  string $subgroup    Subgrupo de la cuenta
     * @param  string $item        Ítem de la cuenta
     * @param  string $generic     Genérica de la cuenta
     * @param  string $specific    Específica de la cuenta
     * @param  string $subspecific Subespecífica de la cuenta
     * @return boolean|AccountingAccount      Retorna falso si no existe una cuenta de nivel superior,
     *                                        de lo contrario obtiene los datos de la misma
     */
    public static function getParent($group, $subgroup, $item, $generic, $specific, $subspecific, $institutional)
    {
        if ($subgroup !== '0') {
            $parent = self::where('group', $group);
            if ($item !== '0') {
                $parent = $parent->where('subgroup', $subgroup);
                if ($generic !== '00') {
                    $parent = $parent->where('item', $item);
                    if ($specific !== '00') {
                        $parent = $parent->where('generic', $generic);
                        if ($subspecific !== '00') {
                            $parent = $parent->where('specific', $specific);
                            if ($institutional !== '000') {
                                $parent = $parent->where('subspecific', $subspecific)
                                                 ->where('institutional', '000');
                            } else {
                                $parent = $parent->where('subspecific', '00')
                                                 ->where('institutional', '000');
                            }
                        } else {
                            $parent = $parent->where('specific', '00');
                        }
                    } else {
                        $parent = $parent->where('generic', '00');
                    }
                } else {
                    $parent = $parent->where('item', '0');
                }
            } else {
                $parent = $parent->where('subgroup', '0');
            }
        }

        if (!isset($parent)) {
            return false;
        }

        return $parent->first();
    }

    /**
     * Establece la relación con las cuentas contables hijas
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(AccountingAccount::class, 'parent_id');
    }

    /**
     * Establece la relación con las cuentas padres
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->BelongsTo(AccountingAccount::class, 'parent_id');
    }

    /**
     * Concatena ciertos valores del registro para generar el código correspondiente
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string código que identifica a la cuenta
     */
    public function getCodeAttribute()
    {
        $group          = $this->group;
        $subgroup       = $this->subgroup;
        $item           = $this->item;
        $generic        = $this->generic;
        $specific       = $this->specific;
        $subspecific    = $this->subspecific;
        $institutional  = $this->institutional;
        return "{$group}.{$subgroup}.{$item}.{$generic}.{$specific}.{$subspecific}.{$institutional}";
    }

    /**
     * Método que obtiene los modelos morfológicos asociados a cuenta patrimonial
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotAccountable()
    {
        return $this->hasMany(Accountable::class, 'accounting_account_id');
    }
}
