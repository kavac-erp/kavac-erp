<?php

namespace Modules\Budget\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class BudgetAccount
 * @brief Datos de cuentas del Clasificador Presupuestario
 *
 * Gestiona el modelo de datos para las cuentas del Clasificador Presupuestario
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetAccount extends Model implements Auditable
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
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'group', 'item', 'generic', 'specific', 'subspecific', 'denomination', 'active', 'resource',
        'egress', 'tax_id', 'parent_id', 'original', 'disaggregate_tax'
    ];

    /**
     * Listado de campos adjuntos a los campos por defecto
     *
     * @var    array $appends
     */
    protected $appends = ['code'];

    /**
     * Lista de atributos ocultos
     *
     * @var array $hidden
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Reescribe el método boot para establecer comportamientos por defecto en la consulta del modelo
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('group', 'asc')
                    ->orderBy('item', 'asc')
                    ->orderBy('generic', 'asc')
                    ->orderBy('specific', 'asc')
                    ->orderBy('subspecific', 'asc');
        });
    }

    /**
     * Obtiene la relación con las cuentas presupuestarias abiertas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountOpens()
    {
        return $this->hasMany(BudgetAccountOpen::class);
    }

    /**
     * Obtiene la relación con la cuenta presupuestaria padre
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountParent()
    {
        return $this->belongsTo(BudgetAccount::class, 'parent_id', 'id');
    }

    /**
     * Obtiene la relación con la cuenta presupuestaria hija
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountChildrens()
    {
        return $this->hasMany(BudgetAccount::class, 'parent_id', 'id');
    }

    /**
     * Obtiene la relación con las cuentas asociadas a modificaciones presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modificationAccounts()
    {
        return $this->hasMany(BudgetModificationAccount::class);
    }

    /**
     * Restringe la eliminación de un registro si el mismo esta relacionado a otro modelo o posee campos
     * que determinan la imposibilidad de su eliminación
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return boolean Devuelve verdadero si la cuenta esta restringida para ser eliminada,
     *                 de lo contrario retorna verdadero
     */
    public function restrictDelete()
    {
        // Se debe agregar a esta comprobación todos los métodos con relación a otro modelo
        return (
            $this->has('account_opens')->get() || $this->has('modificationAccounts')->get() ||
            $this->has('account_converters')->get() || $this->parent_id !== null || $this->original
        );
    }

    /**
     * Método que permite obtener la cuenta asociada de nivel superior
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $group       Grupo de la cuenta
     * @param  string $item        Ítem de la cuenta
     * @param  string $generic     Genérica de la cuenta
     * @param  string $specific    Específica de la cuenta
     * @param  string $subspecific Subespecífica de la cuenta
     *
     * @return boolean|BudgetAccount    Retorna falso si no existe una cuenta de nivel superior,
     *                                  de lo contrario obtiene los datos de la misma
     */
    public static function getParent($group, $item, $generic, $specific, $subspecific)
    {
        if ($item !== '00') {
            $parent = self::where('group', $group);
            if ($generic !== '00') {
                $parent = $parent->where('item', $item);
                if ($specific !== '00') {
                    $parent = $parent->where('generic', $generic);
                    if ($subspecific !== '00') {
                        $parent = $parent->where('specific', $specific);
                    } else {
                        $parent = $parent->where('subspecific', '00');
                    }
                } else {
                    $parent = $parent->where('specific', '00');
                }
            } else {
                $parent = $parent->where('generic', '00');
            }
        }

        if (!isset($parent)) {
            return false;
        }

        return $parent->first();
    }

    /**
     * Método que permite obtener el código de una cuenta presupuestaria
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return string Retorna el código de la cuenta presupuestaria
     */
    public function getCodeAttribute()
    {
        return "{$this->group}.{$this->item}.{$this->generic}.{$this->specific}.{$this->subspecific}";
    }
}
