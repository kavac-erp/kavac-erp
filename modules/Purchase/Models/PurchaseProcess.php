<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PurchaseProcess
 * @brief Gestiona los detalles de los procesos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseProcess extends Model implements Auditable
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
    protected $fillable = ['name', 'description', 'require_documents', 'list_documents'];

    /**
     * Establece la relación con los tipos de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseType()
    {
        return $this->hasMany(PurchaseType::class);
    }

    /**
     * Establece la relación con los planes de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasePlan()
    {
        return $this->hasMany(PurchasePlan::class);
    }
}
