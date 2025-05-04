<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Gender;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollFamilyBurden
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFamilyBurden extends Model implements Auditable
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
     * Lista de relaciones a cargar por defecto
     *
     * @var array $with
     */
    protected $with = ['payrollDisability','payrollRelationship','payrollScholarshipType'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'id_number',
        'birthdate',
        'age',
        'address',
        'payroll_gender_id',
        'payroll_relationships_id',
        'payroll_socioeconomic_id',
        'payroll_schooling_level_id',
        'payroll_scholarship_types_id',
        'study_center',
        'payroll_disability_id',
        'is_student',
        'has_disability',
        'has_scholarships',
        'deleted_at',
    ];

    /**
     * Obtiene la relación con datos socioeconómicos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSocioecomic()
    {
        return $this->belongsTo(PayrollSocioeconomic::class);
    }

    /**
     * Obtiene la relación con datos de niveles de escolaridad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSchoolingLevel()
    {
        return $this->belongsTo(PayrollSchoolingLevel::class);
    }

    /**
     * Obtiene la relación con discapacidad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollDisability()
    {
        return $this->belongsTo(PayrollDisability::class);
    }

    /**
     * Obtiene la relación con datos de la relación laboral
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollRelationship()
    {
        return $this->belongsTo(PayrollRelationship::class, 'payroll_relationships_id');
    }

    /**
     * Obtiene la relación con el género
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollGender()
    {
        return $this->belongsTo(Gender::class, 'payroll_gender_id');
    }

    /**
     * Obtiene la relación con tipos de becas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollScholarshipType()
    {
        return $this->belongsTo(PayrollScholarshipType::class, 'payroll_scholarship_types_id');
    }
}
