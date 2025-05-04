<?php

namespace Modules\Finance\Models;

use App\Models\Currency;
use App\Models\Receiver;
use App\Models\Deduction;
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
 * @class FinancePaymentExecute
 * @brief Modelo de datos para la ejecución de pagos
 *
 * Gestiona el modelo de datos para la ejecución de pagos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePaymentExecute extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'paid_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'paid_at',
        'has_budget',
        'is_partial',
        'source_amount',
        'deduction_amount',
        'paid_amount',
        'pending_amount',
        'completed',
        'observations',
        'status',
        'payment_number',
        'general_bank_reference',
        'document_status_id',
        'currency_id',
        'finance_payment_method_id',
        'finance_bank_account_id',
    ];

    /**
     * Lista de campos personalizados a retornar en las consultas
     *
     * @var array $appends
     */
    protected $appends = ['receiver_id', 'receiver_name', 'receiver_type', 'accounting_entryable', 'is_payroll', 'is_deduction'];

    /**
     * Obtiene los datos del nombre del receptor de una ejecución de pago
     *
     * @return string
     */
    public function getReceiverNameAttribute()
    {
        $payOrder = FinancePayOrderFinancePaymentExecute::with('financePayOrder')->where('finance_payment_execute_id', $this->id)->first();

        if (!$payOrder || !$payOrder->financePayOrder) {
            return '';
        }

        if ($payOrder->financePayOrder->name_sourceable_type == Receiver::class) {
            $receiver = Receiver::find($payOrder->financePayOrder->name_sourceable_id);
        } else {
            $receiver = Receiver::where([
                'receiverable_type' => $payOrder->financePayOrder->name_sourceable_type,
                'receiverable_id' => $payOrder->financePayOrder->name_sourceable_id
            ])->first();
        }
        return $receiver->description;
    }

    /**
     * Obtiene el identificador de un receptor de una ejecución de pago
     *
     * @return integer|string
     */
    public function getReceiverIdAttribute()
    {
        $payOrder = FinancePayOrderFinancePaymentExecute::with('financePayOrder')->where('finance_payment_execute_id', $this->id)->first();

        if (!$payOrder || !$payOrder->financePayOrder) {
            return '';
        }

        if ($payOrder->financePayOrder->name_sourceable_type == Receiver::class) {
            $receiver = Receiver::find($payOrder->financePayOrder->name_sourceable_id);
        } else {
            $receiver = Receiver::where([
                'receiverable_type' => $payOrder->financePayOrder->name_sourceable_type,
                'receiverable_id' => $payOrder->financePayOrder->name_sourceable_id
            ])->first();
        }
        return $receiver->id;
    }

    /**
     * Obtiene el tipo de receptor de una ejecución de pago
     *
     * @return string
     */
    public function getReceiverTypeAttribute()
    {
        $payOrder = FinancePayOrderFinancePaymentExecute::with('financePayOrder')->where('finance_payment_execute_id', $this->id)->first();

        if (!$payOrder || !$payOrder->financePayOrder) {
            return '';
        }

        if ($payOrder->financePayOrder->name_sourceable_type == Receiver::class) {
            $receiver = Receiver::find($payOrder->financePayOrder->name_sourceable_id);
        } else {
            $receiver = Receiver::where([
                'receiverable_type' => $payOrder->financePayOrder->name_sourceable_type,
                'receiverable_id' => $payOrder->financePayOrder->name_sourceable_id
            ])->first();
        }
        return $receiver->receiverable_type;
    }

    /**
     * Obtiene los datos del asiento contable asociado
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function getAccountingEntryableAttribute()
    {
        $accountingEntryable = null;

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accountingEntryable = \Modules\Accounting\Models\AccountingEntryable::with(['accountingEntry' => function ($q) {
                $q->with(['accountingAccounts' => function ($qq) {
                    $qq->with('account');
                }]);
            }])->where([
                'accounting_entryable_type' => FinancePaymentExecute::class,
                'accounting_entryable_id' => $this->id
            ])->first();
        }
        return $accountingEntryable;
    }

    /**
     * Verifica si el registro viene de nómina
     *
     * @param integer $id identificador de la emisión de pago.
     *
     * @return bool  true si el valor es relacionado con la nómina o con el aporte. false en caso contrario
     */
    public function getIsPayrollAttribute(): bool
    {
        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            //Se busca la etapa presupuestaria de la emisión de pago
            $bugetStage = \Modules\Budget\Models\BudgetStage::query()
            ->where([
                'stageable_type'        => FinancePaymentExecute::class,
                'stageable_id'          => $this->id
                ])
            ->where('type', 'PAG')->first();

            //Se busca el compromiso asociado a la emisión de pago
            if (isset($bugetStage)) {
                $compromise = \Modules\Budget\Models\BudgetCompromise::query()
                ->find($bugetStage->budget_compromise_id);

                if (isset($compromise)) {
                    if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                        $CodePayroll = \App\Models\CodeSetting::where("model", \Modules\Payroll\Models\Payroll::class)->first();
                        if (isset($CodePayroll)) {
                            $pattern = '/' . $CodePayroll->format_prefix . '/';
                            if (preg_match($pattern, $compromise->document_number)) {
                                $payroll = \Modules\Payroll\Models\Payroll::query()
                                ->where(
                                    'code',
                                    $compromise->document_number
                                )->first();

                                if (isset($payroll)) {
                                    $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
                                    if (isset($payrollPaymentPeriod)) {
                                        return $payrollPaymentPeriod->payrollPaymentType->order;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Verifica si el registro es de una retención
     *
     * @return bool
     */
    public function getIsDeductionAttribute(): bool
    {
        foreach ($this->financePayOrders()->get() as $payOrder) {
            if ($payOrder->document_sourceable_type == Deduction::class) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene la relación con las órdenes de pago
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function financePayOrders()
    {
        return $this->belongsToMany(FinancePayOrder::class)->withTimestamps();
    }

    /**
     * Obtiene la relación con las deducciones pagadas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financePaymentDeductions()
    {
        return $this->hasMany(FinancePaymentDeduction::class);
    }

    /**
     * Obtiene la relación con la ejecución de pago de impuesto (IVA)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function financePaymentExecuteIva()
    {
        return $this->hasOne(FinancePaymentExecuteIva::class);
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
     * Obtiene la relación con el método de pago
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePaymentMethod()
    {
        return $this->belongsTo(FinancePaymentMethods::class, 'finance_payment_method_id');
    }

    /**
     * Obtiene la relación con la cuenta bancaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBankAccount()
    {
        return $this->belongsTo(FinanceBankAccount::class);
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->paid_at;
    }

    /**
     * Scope para buscar y filtrar datos de emisiones de pago
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
            ->orWhereRaw("TO_CHAR(paid_at, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'")
            ->orWhere(DB::raw('upper(observations)'), 'LIKE', '%' . strtoupper($search) . '%');
    }
}
