<?php

namespace Modules\Budget\Models;

use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class BudgetSubSpecificFormulation
 * @brief Datos de las formulaciones de presupuesto por sub específicas
 *
 * Gestiona el modelo de datos para las formulaciones de presupuesto por sub específicas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSubSpecificFormulation extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Establece las relaciones por defecto que se retornan con las consultas
     *
     * @var array $with
     */
    protected $with = ['specificAction', 'currency'];

    /**
     * Lista con campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'date',
        'code',
        'year',
        'total_formulated',
        'assigned',
        'budget_specific_action_id',
        'currency_id',
        'institution_id',
        'document_status_id',
        'budget_financement_type_id',
        'budget_financement_source_id',
        'financement_amount'
    ];

    /**
     * Las formulaciones de presupuesto tienen un tipo de financiamiento.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetFinancementType()
    {
        return $this->belongsTo(BudgetFinancementTypes::class);
    }

    /**
     * Las formulaciones de presupuesto tienen una fuente de financiamiento.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetFinancementSource()
    {
        return $this->belongsTo(BudgetFinancementSources::class);
    }

    /**
     * Establece la relación con la acción especifica
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specificAction()
    {
        return $this->belongsTo(BudgetSpecificAction::class, 'budget_specific_action_id');
    }

    /**
     * Establece la relación con la moneda
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
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
     * Establece la relación con el estatus del documento
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
     * Establece la relación con las cuentas presupuestarias aperturadas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountOpens()
    {
        return $this->hasMany(BudgetAccountOpen::class);
    }

    /**
     * Establece la relación con las cuentas de modificaciones presupuestarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modificationAccounts()
    {
        return $this->hasMany(BudgetModificationAccount::class);
    }

    /**
     * Establece la relación con los detalles de los compromisos presupuestarios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCompromiseDetail()
    {
        return $this->hasMany(BudgetCompromiseDetail::class);
    }

    /**
     * Método que permite validar si una formulación ya existe con los mismos datos a registrar, en cuyo caso
     * retorna verdadero
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  array  $data Campos a filtrar en la consulta
     *
     * @return boolean      Devuelve verdadero si la formulación existe, de lo contrario retorna falso
     */
    public static function validateStore($data = [])
    {
        if (is_array($data) && !empty($data)) {
            $exists = self::where('institution_id', $data['institution_id'])
                          ->where('budget_specific_action_id', $data['budget_specific_action_id'])
                          ->where('currency_id', $data['currency_id'])->where('year', $data['year'])->first();
            return (!$exists);
        }

        return true;
    }

    /**
     * Scope para obtener la formulación de presupuesto vigente
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  object $query               Consulta del modelo
     * @param  integer $specific_action_id Identificador de la acción específica por la cual realizar el filtro
     *
     * @return Builder                      Objeto con la consulta solicitada
     */
    public function scopeCurrentFormulation($query, $specific_action_id)
    {
        /* Objeto con información referente al estado del documento */
        $documentStatus = DocumentStatus::where('action', 'AP')->first();

        return $query->where('budget_specific_action_id', $specific_action_id)
                     ->where('document_status_id', $documentStatus->id)
                     ->where('assigned', true)
                     ->orderBy('year', 'desc')->first();
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
