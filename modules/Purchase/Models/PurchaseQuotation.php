<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PurchaseQuotation
 * @brief Gestiona la información, procesos, consultas y relaciones de las cotizaciones de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseQuotation extends Model implements Auditable
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
    protected $fillable = [
        'code',
        'status',
        'purchase_supplier_id',
        'purchase_base_budget_id',
        'currency_id',
        'subtotal',
        'date',
        'orderable_type',
        'orderable_id'
    ];

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $appends
     */
    protected $appends = ['status_purchase_order'];

    /**
     * Obtiene el estado de la orden de compra
     *
     * @return string
     */
    public function getStatusPurchaseOrderAttribute()
    {
        $purchase_order = $this->purchaseDirectHire()->first();
        return $purchase_order ? $purchase_order->status : "";
    }

    /**
     * Establece la relación con el proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplier()
    {
        return $this->belongsTo(PurchaseSupplier::class);
    }

    /**
     * Establece la relación con la moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Establece la relación con la contratación directa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseDirectHire()
    {
        return $this->belongsTo(PurchaseDirectHire::class, "orderable_id");
    }
    /**
     * Establece la relación con la tabla pivote de los registros asociados
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotRecordable()
    {
        return $this->hasMany(Pivot::class, 'recordable_id');
    }

    /**
     * Establece la relación morfológica con los items de los requerimientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatable()
    {
        return $this->morphMany(PurchasePivotModelsToRequirementItem::class, 'relatable');
    }

    /**
     * Obtiene todos los documentos asociados al proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Scope para buscar y filtrar datos de cotizaciones
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->where(DB::raw('upper(code)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhereRaw("TO_CHAR(date, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'");
    }
}
