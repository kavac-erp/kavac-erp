<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Purchase\Models\RequiredDocument;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PurchaseType
 * @brief Gestiona la información, procesos, consultas y relaciones de ls tipos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseType extends Model implements Auditable
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
    protected $fillable = ['name', 'description', 'purchase_processes_id', 'documents_id'];

     /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $appends
     */
    protected $appends = ['documents'];

    /**
     * Metodo Accessors para obtener todos los documentos requeridos
     *
     * @return \Modules\Purchase\Models\RequiredDocument[]|void
     */
    public function getDocumentsAttribute()
    {
        if ($this->documents_id) {
            $documents_id = json_decode($this->documents_id, true);
            return RequiredDocument::whereIn('id', $documents_id)->get();
        }
    }
    /**
     * Establece la relación con los procesos de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseProcess()
    {
        return $this->belongsTo(PurchaseProcess::class, 'purchase_processes_id');
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
