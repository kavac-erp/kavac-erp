<?php

namespace Modules\Budget\Models;

use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class BudgetSpecificAction
 * @brief Datos de Acciones Específicas
 *
 * Gestiona el modelo de datos para las Acciones Específicas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSpecificAction extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Establece las relaciones por defecto que se retornan con las consultas
     *
     * @var array $with
     */
    protected $with = ['specificable'];

    /**
     * Lista con campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'from_date', 'to_date'];

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = ['from_date', 'to_date', 'code', 'name', 'description', 'active'];


    /**
     * Crea un campo para obtener el nombre de la institución
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return string Devuelve el nombre de la institución asociada a la acción específica
     */
    public function getInstitutionAttribute()
    {
        return $this->specificable->department->institution->name;
    }

    /**
     * Crea un campo para obtener el tipo de registro asociado (Proyecto o Acción Centralizada)
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return string Devuelve el tipo de registro asociado
     */
    public function getTypeAttribute()
    {
        $type = str_replace("Modules\Budget\Models\\", "", $this->specificable_type);

        return ($type === "BudgetCentralizedAction") ? "Acción Centralizada" : "Proyecto";
    }

    /**
     * Establece la relación morfológica con las acciones específicas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function specificable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación con las formulaciones presupuestarias
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subSpecificFormulations()
    {
        return $this->hasMany(BudgetSubSpecificFormulation::class, 'budget_specific_action_id');
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->from_date;
    }
}
