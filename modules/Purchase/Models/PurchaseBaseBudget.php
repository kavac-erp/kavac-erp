<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Composer\XdebugHandler\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;
use Nwidart\Modules\Facades\Module;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class PurchaseBaseBudget extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
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

    protected $appends = [
        'availability',
        'availabilityitem',

        //Estado auxiliar para de terminar en que estatus se encuentra el presupuesto base
        'status_aux'
    ];

    public function getAvailabilityAttribute()
    {
        $answer = "";
        $availability = PurchaseBudgetaryAvailability::where('purchase_base_budgets_id', $this->id)->first();
        if ($availability) {
            if ($availability->availability == 1) {
                $answer = "Disponible";
            } else {
                $answer = "No_Disponible";
            };
        }
        return $answer;
    }

    public function getAvailabilityItemAttribute()
    {
        $availability = PurchaseBudgetaryAvailability::where('purchase_base_budgets_id', $this->id)->get()->toArray();
        return $availability;
    }

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

        // dd($staus_quotations, $staus_quotations_wait, $staus_quotations_approved);
        switch ($this->status) {
            case 'WAIT':
                $status = 'WAIT';
                break;
            case 'WAIT_QUOTATION':
                if (!$this->send_notify) {
                    $status = 'WAIT_SEND_NOTIFICATION';
                } elseif (strlen($this->availability) == 0 && $this->send_notify) {
                    $status = 'WAIT_BUDGET_AVAILABILITY';
                } elseif (strlen($this->availability) != 0) {
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
        return $status;
        // WAIT -> Por completar
        // WAIT_BUDGET_AVAILABILITY -> Esper por disponibilidad presupuestria
        // WAIT_QUOTATION -> Espera por cotización
        // WAIT_APPROVE_PARTIAL_QUOTE -> Espera por aprobar cotización parcial
        // WAIT_APPROVE_QUOTE -> Espera por aprobar cotización
        // PARTIALLY_QUOTED -> Cotizado parcialmente
        // QUOTED -> Cotizado
        // BOUGHT -> Comprado
    }


    /**
     * PurchaseBaseBudget belongs to Currency.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        // belongsTo(RelatedModel, foreignKey = currency_id, keyOnRelatedModel = id)
        return $this->belongsTo(Currency::class);
    }

    /**
     * PurchaseBaseBudget belongs to Tax.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tax()
    {
        // belongsTo(RelatedModel, foreignKey = tax_id, keyOnRelatedModel = id)
        return $this->belongsTo(Tax::class);
    }
    /**
     * PurchaseBaseBudget has one PurchaseRequirement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchaseRequirement()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = purchaseBaseBudget_id, localKey = id)
        return $this->hasOne(PurchaseRequirement::class);
    }

    /**
     * PurchaseBaseBudget morphs many PurchasePivotModelsToRequirementItem.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatable()
    {
        // morphMany(MorphedModel, morphableName, type = able_type, relatedKeyName = able_id, localKey = id)
        return $this->morphMany(PurchasePivotModelsToRequirementItem::class, 'relatable');
    }

    /**
     * PurchaseBaseBudget has many Pivot.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotRelatable()
    {
        // hasMany(RelatedModel, foreignKeyOnRelatedModel = purchaseBaseBudget_id, localKey = id)
        return $this->hasMany(Pivot::class, 'relatable_id');
    }

    /**
     * PurchaseBaseBudget morphs to models in orderable_type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function orderable()
    {
        // morphTo($name = orderable, $type = orderable_type, $id = orderable_id)
        // requires orderable_type and orderable_id fields on $this->table
        return $this->morphTo();
    }

    /**
     * PurchaseBaseBudget has many purchaseQuotation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseQuotations()
    {
        return $this->hasMany(PurchaseQuotation::class);
    }

    /**
     * PurchaseDirectHire belongs to payroll_employment.
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
     * PurchaseDirectHire belongs to payroll_employment.
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
     * PurchaseDirectHire belongs to payroll_employment.
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
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firstSignature()
    {
        // belongsTo(RelatedModel, foreignKey = payroll_employment_id, keyOnRelatedModel = id)
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'first_signature_id'
                ) : null;
    }
    /**
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function secondSignature()
    {
        // belongsTo(RelatedModel, foreignKey = payroll_employment_id, keyOnRelatedModel = id)
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
}
