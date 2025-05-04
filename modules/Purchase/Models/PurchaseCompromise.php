<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PurchaseCompromise
 * @brief Datos de los compromisos de compras
 *
 * Gestiona el modelo de datos para los Compromisos de Compras
 * Este modelo es usado en caso de que no se encuentre instalado el modulo de Presupuesto
 *
 * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseCompromise extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'compromised_at'];

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = ['compromised_at', 'description', 'code', 'document_status_id'];

    /**
     * Establece la relación morfológica con el compromiso
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function compromiseable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación morfológica con la fuente del compromiso
     * Este método requiere que la fuente asociada contenga un campo llamado code con el código del documento
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sourceable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación con los detalles del compromiso
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCompromiseDetails()
    {
        return $this->hasMany(PurchaseCompromiseDetail::class);
    }

    /**
     * Establece la relación con los estados de una ejecución presupuestaria
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetStages()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(BudgetStage::class) : [];
    }

    /**
     * Establece la relación con el estatus del documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }
}
