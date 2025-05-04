<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Gender;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollFamilyBurden
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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
     * @var array $dates
     */
    protected $dates = ['deleted_at'];
    protected $with = ['payrollDisability','payrollRelationship','payrollScholarshipType'];
    /**
     * Lista de atributos que pueden ser asignados masivamente
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

    public function payrollSocioecomic()
    {
        return $this->belongsTo(PayrollSocioeconomic::class);
    }

    public function payrollSchoolingLevel()
    {
        return $this->belongsTo(PayrollSchoolingLevel::class);
    }

    public function payrollDisability()
    {
        return $this->belongsTo(PayrollDisability::class);
    }

    public function payrollRelationship()
    {
        return $this->belongsTo(PayrollRelationship::class, 'payroll_relationships_id');
    }

    public function payrollGender()
    {
        return $this->belongsTo(Gender::class, 'payroll_gender_id');
    }

    public function payrollScholarshipType()
    {
        return $this->belongsTo(PayrollScholarshipType::class, 'payroll_scholarship_types_id');
    }
}
