<?php

namespace Modules\Budget\Models;

use App\Models\Source;
use App\Models\Receiver;
use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\FinancePayOrder;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class BudgetCompromise
 * @brief Datos de los compromisos presupuestarios
 *
 * Gestiona el modelo de datos para los Compromisos de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetCompromise extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista con campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'compromised_at'];

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'compromised_at', 'description', 'code', 'document_number', 'institution_id', 'document_status_id',
        'sourceable_type', 'sourceable_id', 'compromiseable_type', 'compromiseable_id'
    ];

    /**
     * Listado de campos adjuntos a los campos por defecto
     *
     * @var    array $appends
     */
    protected $appends = ['status', 'receiver'];

    /**
     * Establece la relación morfológica con compromisos
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
     * Establece la relación morfológica con las fuentes de documentos
     *
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
        return $this->hasMany(BudgetCompromiseDetail::class);
    }

    /**
     * Establece la relación con los estatus presupuestarios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetStages()
    {
        return $this->hasMany(BudgetStage::class);
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

    /**
     * Establece la relación con la institución
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * financePayOrders belongs to FinancePayOrder.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePayOrders()
    {
        if (Module::has('Finance') && Module::isEnabled('Finance')) {
            return $this->morphMany(FinancePayOrder::class, 'document_sourceable');
        } else {
            return [];
        }
    }

    /**
     * Método que permite obtener el estatus de un compromiso
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return string Retorna el estatus de un compromiso
     */
    public function getStatusAttribute()
    {
        $stages = $this->budgetStages()->orderBy('type', 'asc')->get();
        $status_action = $this->documentStatus->action;

        if ($status_action == 'AN') {
            // Anulado
            return 'AN';
        }

        $status = $status_action;
        $stagesPagAmount = 0;
        $stagesComAmount = 0;

        if (Module::has('Finance') && Module::isEnabled('Finance')) {
            foreach ($stages as $stage) {
                if ($stage->type == 'CAU') {
                    $status = $stage->type ;
                } elseif ($stage->type == 'PAG') {
                    $stagesPagAmount += $stage->amount;
                } elseif ($stage->type == 'COM') {
                    $stagesComAmount += $stage->amount;
                }
            }

            if ($stagesComAmount > 0 && $stagesPagAmount > 0 && (string)$stagesPagAmount == (string)$stagesComAmount) {
                $status = 'PA';
            } elseif ($status_action == 'PR' || $status_action == 'EL') {
                // En Proceso o Elaborado El estatus queda pendiente
                $status = 'PE';
            }
        }

        return $status;
    }

    /**
     * Método que permite obtener el beneficiario de un compromiso
     * en caso que el compromiso sea manual.
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return string Retorna el beneficiario del compromiso
     */
    public function getReceiverAttribute()
    {
        $source = Source::query()
            ->with('receiver.associateable')
            ->where('sourceable_id', $this->id)
            ->where('sourceable_type', BudgetCompromise::class)
            ->orderBy('id', 'desc')
            ->first();

        $receiver = $source ? $source->receiver : null;

        if ($receiver == null) {
            if (Module::has('Purchase') && Module::isEnabled('Purchase')) {
                $purchaseReceiver = \Modules\Purchase\Models\PurchaseDirectHire::with('purchaseSupplier')
                    ->where('code', $this->document_number)
                    ->first();
                if ($purchaseReceiver != null) {
                    $supplierId = $purchaseReceiver->purchaseSupplier->id;

                    $receiver = Receiver::with(['receiverable', 'associateable'])
                        ->where('receiverable_id', $supplierId)
                        ->where('receiverable_type', 'Modules\Purchase\Models\PurchaseSupplier')
                        ->first();
                }
            }
        }

        return $receiver;
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->compromised_at;
    }

    /**
     * Scope para buscar y filtrar datos de emisiones de pago
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->where(DB::raw('upper(code)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhereRaw("TO_CHAR(compromised_at, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'")
            ->orWhere(DB::raw('upper(description)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhere(DB::raw('upper(document_number)'), 'LIKE', '%' . strtoupper($search) . '%');
    }
}
