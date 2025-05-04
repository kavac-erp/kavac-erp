<?php

namespace Modules\Budget\Models;

use App\Models\Receiver;
use App\Models\Source;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

/**
 * @class BudgetCompromise
 * @brief Datos de los compromisos presupuestarios
 *
 * Gestiona el modelo de datos para los Compromisos de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetCompromise extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'compromised_at'];

    protected $fillable = [
        'compromised_at', 'description', 'code', 'document_number', 'institution_id', 'document_status_id',
        'sourceable_type', 'sourceable_id', 'compromiseable_type', 'compromiseable_id'
    ];

    /**
     * Listado de campos adjuntos a los campos por defecto
     *
     * @var    array
     */
    protected $appends = ['status', 'receiver'];

    /**
     * Compromise morphs to models in compromiseable_type.
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function compromiseable()
    {
        return $this->morphTo();
    }

    /**
     * Compromise morphs to models in sourceable_type.
     *
     * Este método requiere que la fuente asociada contenga un campo llamado code con el código del documento
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function sourceable()
    {
        return $this->morphTo();
    }

    /**
     * BudgetCompromise has many BudgetCompromiseDetail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCompromiseDetails()
    {
        return $this->hasMany(BudgetCompromiseDetail::class);
    }

    /**
     * BudgetCompromise has many BudgetStages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetStages()
    {
        return $this->hasMany(BudgetStage::class);
    }

    /**
     * BudgetCompromise belongs to DocumentStatus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * BudgetModifications belongs to Institution.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que permite obtener el estatus de un compromiso
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return string Retorna el estatus de un compromiso
     */
    public function getStatusAttribute()
    {
        $stages = $this->budgetStages()->orderBy('type', 'asc')->get();
        $status_action = $this->documentStatus->action;
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
     * @method    scopeSearch
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Database\Eloquent\Builder
     * @param  string         $search    Cadena de texto a buscar
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->where(DB::raw('upper(code)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhereRaw("TO_CHAR(compromised_at, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'")
            ->orWhere(DB::raw('upper(description)'), 'LIKE', '%' . strtoupper($search) . '%');
    }
}
