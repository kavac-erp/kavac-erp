<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;

/**
 * @class PurchaseBaseBudget
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseBaseBudget extends Model implements Auditable
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
        'currency_id',
        'date',
        'subtotal',
        'tax_id',
        'orderable_type',
        'orderable_id',
        'prepared_by_id',
        'reviewed_by_id',
        'verified_by_id',
        'first_signature_id',
        'second_signature_id',
        'status',
        'send_notify'
    ];

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array
     */
    protected $appends = [
        'availability',
        'availabilityitem',
        'purchaseBudgetaryAvailabilityDocument',

        //Estado auxiliar para determinar en que estatus se encuentra el presupuesto base
        'status_aux'
    ];

    /**
     * Obtiene la información del archivo de documento de la disponibilidad presupuestaria
     *
     * @return Document|Model|object|null
     */
    public function getPurchaseBudgetaryAvailabilityDocumentAttribute()
    {
        $documentFile = Document::where([
            'documentable_type' => 'Modules\Purchase\Models\PurchaseBudgetaryAvailability',
            'documentable_id' => $this->id
        ])->first();
        return $documentFile;
    }

    /**
     * Obtiene la disponibilidad presupuestaria del presupuesto base
     *
     * @return string
     */
    public function getAvailabilityAttribute()
    {
        $answer = "";
        $availability = PurchaseBudgetaryAvailability::where('purchase_base_budgets_id', $this->id)->first();
        if ($availability) {
            if ($availability->availability == 1) {
                $answer = "Disponible";
            } elseif ($availability->availability == 2) {
                $answer = "AP"; //Aprobado/
            } else {
                $answer = "No_Disponible";
            };
        }
        return $answer;
    }

    /**
     * Obtiene los detalles de la disponibilidad presupuestaria del presupuesto base
     *
     * @return array
     */
    public function getAvailabilityItemAttribute()
    {
        $availability = PurchaseBudgetaryAvailability::where('purchase_base_budgets_id', $this->id)->get()->toArray();
        return $availability;
    }

    /**
     * Obtiene el estatus del presupuesto base
     *
     * @return string
     */
    public function getStatusAuxAttribute()
    {
        $status = 'WAIT';
        $quotations = $this->pivotRelatable()->with(['recordable' => function ($q) {
        }])->get();
        //Cantidad total de Cotizaciones asociadas a un presupuesto base
        $staus_quotations = $quotations->count();
        //Cantidad de Regitros de cotizaciones en espera de ser aprobadas
        $staus_quotations_wait = 0;
        //Ceantidad de Regitros de cotizaciones aprobadas
        $staus_quotations_approved = 0;


        foreach ($quotations as $quotation) {
            if ($quotation->recordable) {
                if ($quotation->recordable->status == 'QUOTED') {
                    $staus_quotations_wait++;
                } elseif ($quotation->recordable->status == 'APPROVED') {
                    $staus_quotations_approved++;
                }
            }
        }

        switch ($this->status) {
            case 'WAIT':
                $status = 'WAIT';
                break;
            case 'WAIT_QUOTATION':
                if (!$this->send_notify) {
                    $status = 'WAIT_SEND_NOTIFICATION';
                } elseif (($this->availability == "" || $this->availability == 'No_Disponible') && $this->send_notify) {
                    $status = 'WAIT_BUDGET_AVAILABILITY';
                } elseif ($this->availability == 'Disponible' && $this->send_notify) {
                    $status = 'WAIT_APPROVE_BUDGET_AVAILABILITY';
                } elseif ($this->availability == 'AP' && $this->send_notify) {
                    $status = 'WAIT_QUOTATION';
                }
                break;
            case 'PARTIALLY_QUOTED':
                $status = $staus_quotations_wait > 0
                            ? 'WAIT_APPROVE_PARTIAL_QUOTE'
                            : ($staus_quotations_approved > 0
                                ? 'PARTIALLY_QUOTED' : '');

                break;
            case 'QUOTED':
                if ($staus_quotations == $staus_quotations_wait) {
                    $status = 'WAIT_APPROVE_QUOTE';
                } elseif ($staus_quotations == $staus_quotations_approved) {
                    $status = 'QUOTED';
                } elseif ($staus_quotations > $staus_quotations_wait && $staus_quotations_wait >= 1) {
                    $status = 'WAIT_APPROVE_PARTIAL_QUOTE';
                }
                break;
            default:
                $status = 'BOUGHT';
                break;
        }

        // WAIT -> Por completar
        // WAIT_BUDGET_AVAILABILITY -> Espera por disponibilidad presupuestria
        // WAIT_APPROVE_BUDGET_AVAILABILITY -> Espera por aprobar disponibilidad presupuestria
        // WAIT_QUOTATION -> Espera por cotización
        // WAIT_APPROVE_PARTIAL_QUOTE -> Espera por aprobar cotización parcial
        // WAIT_APPROVE_QUOTE -> Espera por aprobar cotización
        // PARTIALLY_QUOTED -> Cotizado parcialmente
        // QUOTED -> Cotizado
        // BOUGHT -> Comprado
        return $status;
    }

    /**
     * Establece la relación con el tipo de moneda asociada a un presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Establece la relación con el tipo de impuesto asociado a un presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Establece la relación con el requerimiento de compra asociado a un presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchaseRequirement()
    {
        return $this->hasOne(PurchaseRequirement::class);
    }

    /**
     * Establece la relación con los elementos de requerimiento de compra asociados a un presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatable()
    {
        return $this->morphMany(PurchasePivotModelsToRequirementItem::class, 'relatable');
    }

    /**
     * Establece la relación con el modelo pivot
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotRelatable()
    {
        return $this->hasMany(Pivot::class, 'relatable_id');
    }

    /**
     * Establece la relación morfológica con la orden
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function orderable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación con las cotizaciones asociadas a un presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseQuotations()
    {
        return $this->hasMany(PurchaseQuotation::class);
    }

    /**
     * Establece la relación con el personal que preparó el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preparedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'prepared_by_id'
                ) : null;
    }

    /**
     * Establece la relación con el personal que revisó el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'reviewed_by_id'
                ) : null;
    }

    /**
     * Establece la relación con el personal que verificó el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifiedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'verified_by_id'
                ) : null;
    }

    /**
     * Establece la relación con el personal que firmó, en primer lugar, el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firstSignature()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'first_signature_id'
                ) : null;
    }

    /**
     * Establece la relación con el personal que firmó, en segundo lugar, el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function secondSignature()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'second_signature_id'
                ) : null;
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
     * Scope para buscar y filtrar datos de presupuestos bases
     *
     * @method    scopeSearch
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar

     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->whereRaw("TO_CHAR(date, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'")
            ->orWhereHas('purchaseRequirement', function ($query) use ($search) {
                $query->where('code', 'ilike', '%' . $search . '%');
            })
            ->orWhereHas('currency', function ($query) use ($search) {
                $query->where('symbol', 'ilike', '%' . $search . '%')
                    ->orWhere('name', 'ilike', '%' . $search . '%');
            });
    }
}
