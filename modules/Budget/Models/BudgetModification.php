<?php

namespace Modules\Budget\Models;

use App\Models\Document;
use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class BudgetModification
 * @brief Datos de las modificaciones presupuestarias
 *
 * Gestiona el modelo de datos para las modificaciones presupuestarias (Crédito Adicional, Traspasos y Reducciones)
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @property integer $id Identificador de la modificación presupuestaria
 * @property integer $currency_id Identificador de la moneda
 * @property string $code Código de la modificación presupuestaria
 * @property string $type Tipo de la modificación presupuestaria
 * @property string $description Descripción de la modificación presupuestaria
 * @property string $document Documento de la modificación presupuestaria
 * @property string $institution_id Identificador de la institución
 * @property string $document_status_id Identificador del estado del documento
 * @property string $status Estatus de la modificación presupuestaria
 * @property string $approved_at Fecha de aprobación de la modificación presupuestaria
 * @property BudgetModificationAccount $budgetModificationAccounts Cuentas de la modificación presupuestaria
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetModification extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Establece las relaciones por defecto que se retornan con las consultas
     *
     * @var array $with
     */
    protected $with = [
        'budgetModificationAccounts',
        'institution',
        'currency',
        'documentFile'
    ];

    /**
     * Lista con campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'approved_at'];

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'approved_at',
        'code',
        'currency_id',
        'type',
        'description',
        'document',
        'institution_id',
        'document_status_id',
        'status'
    ];

    /**
     * Establece la relación con las cuentas presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetModificationAccounts()
    {
        return $this->hasMany(BudgetModificationAccount::class);
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
     * Establece la relación con la moneda
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Establece la relación con el estatus de documento
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * Establece la relación con un archivo de documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function documentFile()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->approved_at;
    }
}
