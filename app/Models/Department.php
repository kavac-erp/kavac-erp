<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Department
 * @brief Datos de las Unidades, Departamentos o Dependencias
 *
 * Gestiona el modelo de datos para las Unidades, Departamentos o Dependencias
 *
 * @property  string|integer  $id
 * @property  string  $name
 * @property  string  $acronym
 * @property  string  $hierarchy
 * @property  boolean $issue_requests
 * @property  boolean $active
 * @property  boolean $administrative
 * @property  integer $parent_id
 * @property  integer $institution_id
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Department extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de relaciones a incorporar en las consultas
     *
     * @var    array $with
     */
    protected $with = ['institution'];

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
        'name',
        'acronym',
        'hierarchy',
        'issue_requests',
        'active',
        'administrative',
        'parent_id',
        'institution_id'
    ];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Método que obtiene el departamento adscrito a otro departamento
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Método que obtiene todos los departamentos adscritos a un departamento
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childrens()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Método que obtiene la institución a la que pertenece el departamento
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Unidades o Dependencias pueden tener muchas coordinaciones.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coordination()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->hasMany(\Modules\Payroll\Models\PayrollCoordination::class) : [];
    }
}
