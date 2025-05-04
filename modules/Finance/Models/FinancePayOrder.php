<?php

namespace Modules\Finance\Models;

use App\Models\Currency;
use App\Models\Receiver;
use App\Models\Institution;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class FinancePayOrder
 * @brief Modelo de datos para las ordenes de pago
 *
 * Gestiona el modelo de datos para las ordenes de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePayOrder extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'ordered_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'ordered_at',
        'type',
        'is_partial',
        'pending_amount',
        'completed',
        'document_type',
        'document_number',
        'source_amount',
        'amount',
        'concept',
        'observations',
        'status',
        'budget_specific_action_id',
        'institution_id',
        'document_status_id',
        'currency_id',
        'name_sourceable_type',
        'name_sourceable_id',
        'document_sourceable_type',
        'document_sourceable_id',
        'month',
        'period',
    ];

    /**
     * Lista de campos personalizados a retornar en las consultas
     *
     * @var array $appends
     */
    protected $appends = ['accounting_entryable',
        'receiver_name',
        'status_aux',
        'status_payment_execute',
        'is_payroll_contribution',
    ];

    /**
     * Obtiene el nombre del receptor de una orden de pago
     *
     * @return string
     */
    public function getReceiverNameAttribute()
    {
        if ($this->name_sourceable_type != Receiver::class) {
            $receiver = Receiver::where([
                'receiverable_type' => $this->name_sourceable_type,
                'receiverable_id' => $this->name_sourceable_id
            ])->first();
        } else {
            $receiver = Receiver::find($this->name_sourceable_id);
        }

        return $receiver ? $receiver->description : '';
    }

    /**
     * Obtiene el id del receptor de una orden de pago
     *
     * @return integer|string
     */
    public function scopeReceiverId()
    {
        if ($this->name_sourceable_type != Receiver::class) {
            $receiver = Receiver::where([
                'receiverable_type' => $this->name_sourceable_type,
                'receiverable_id' => $this->name_sourceable_id
            ])->first();
        } else {
            $receiver = Receiver::find($this->name_sourceable_id);
        }

        return $receiver ? $receiver->id : '';
    }

    /**
     * Obtiene los datos del asiento contable asociado
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return object
     */
    public function getAccountingEntryableAttribute()
    {
        $accountingEntryable = null;

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accountingEntryable = \Modules\Accounting\Models\AccountingEntryable::query()
                ->with(
                    'accountingEntry.accountingAccounts.account' .
                    ':id,group,generic,subspecific,subgroup,specific,denomination,item,institutional'
                )
                ->where([
                    'accounting_entryable_type' => FinancePayOrder::class,
                    'accounting_entryable_id' => $this->id
                ])
                ->select('accounting_entry_id')
                ->first();
        }
        return $accountingEntryable;
    }

    /**
     * Obtiene el estatus de una orden de pago según varias caracteristicas
     *
     * @author Francisco J. P. Ruíz <fpenya@cenditel.gob.ve>
     *
     * @return string
     */
    public function getStatusAuxAttribute()
    {
        $document_status = $this->documentStatus()->first();
        $status = '';

        if ($this->status === 'PA') {
            return $status = 'PA';
        }
        if ($document_status && $document_status->action === 'AN') {
            return $status = 'AN';
        }
        if ($document_status && $document_status->action === 'RE') {
            return $status = 'RE';
        }
        if ($document_status && $document_status->action === 'AP') {
            return $status = 'AP';
        }
        if ($this->status === 'PE') {
            return $status = 'PE';
        }
        return $status;
    }

    /**
     * Obtiene el estatus de emisión de pago relacionada a este modelo
     *
     * @author Francisco J. P. Ruíz <fpenya@cenditel.gob.ve>
     *
     * @return string
     */
    public function getStatusPaymentExecuteAttribute()
    {
        $flag1 = false; //Pagado
        $flag2 = false; //Pendiente.
        $flag3 = false; //Anulado
        $paymentExecute = $this->financePaymentExecute()->get();
        if (isset($paymentExecute)) {
            foreach ($paymentExecute as $payExec) {
                if ($payExec->status == 'PP' || $payExec->status == 'PA') {
                    $flag1 = true;
                } elseif ($payExec->status == 'AN') {
                    $flag3 = true;
                } elseif ($payExec->status == 'PE') {
                    $flag2 = true;
                }
            }
        }
        if ($flag1) {
            return 'PA';
        }
        if ($flag2) {
            return 'PE';
        }
        if ($flag3) {
            return 'AN';
        }
        return ""; //No hay emisiones de pagos relacionadas.
    }

    /**
     * Verifica si el registro viene de un aporte de nómina
     *
     * @param integer $id identificador de la emisión de pago.
     *
     * @return bool  true si el valor es relacionado con el aporte de nómina, false en caso contrario
     */
    public function getIsPayrollContributionAttribute(): bool
    {
        //Se busca el compromiso asociado a la orden de pago
        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $compromise = \Modules\Budget\Models\BudgetCompromise::query()
            ->find($this->document_sourceable_id);

            if (isset($compromise)) {
                if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                    $CodePayroll = \App\Models\CodeSetting::where("model", \Modules\Payroll\Models\Payroll::class)->first();
                    if (isset($CodePayroll)) {
                        $regexPattern = '/^AP - \\d+' . $CodePayroll?->format_prefix . '/';
                        if (preg_match($regexPattern, $compromise->document_number)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Obtiene la relación con la acción especifica del módulo de presupuesto si está presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetSpecificAction()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetSpecificAction::class) : [];
    }

    /**
     * Obtiene la relación con la institución
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Obtiene la relación con el estatus del documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }

    /**
     * Obtiene la relación con la moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Obtiene la relación morfológica con el documento fuente (que generó el proceso) para la orden de pago
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function documentSourceable()
    {
        return $this->morphTo();
    }

    /**
     * Obtiene la relación morfológica con el nombre fuente (que generó el proceso) para la orden de pago
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function nameSourceable()
    {
        return $this->morphTo();
    }

    /**
     * Obtiene la relación con las ejecuciones de pago
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function financePaymentExecute()
    {
        return $this->belongsToMany(FinancePaymentExecute::class)->withTimestamps();
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->ordered_at;
    }

    /**
     * Scope para buscar y filtrar datos de ordenes de pago
     *
     * @method    scopeSearch
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query     Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar

     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->where(DB::raw('upper(code)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhereRaw("TO_CHAR(ordered_at, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'")
            ->orWhere(DB::raw('upper(concept)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhere(DB::raw('amount'), 'LIKE', '%' . strtoupper($search) . '%');
    }
}
