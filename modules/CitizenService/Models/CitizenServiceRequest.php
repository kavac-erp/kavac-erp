<?php

namespace Modules\CitizenService\Models;

use App\Models\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Modules\Payroll\Models\PayrollNationality;
use Modules\Payroll\Models\PayrollStaff;

/**
 * @class CitizenService
 * @brief Datos de información de ingresar solicitud
 *
 * Gestiona el modelo de ingresar solicitud
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequest extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de relaciones a cargar por defecto
     *
     * @var array $with
     */
    protected $with = ['city', 'parish'];

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
        'code', 'gender_id', 'gender', 'nationality_id', 'nationality', 'community',
        'location', 'commune', 'communal_council', 'population_size', 'director_id',
        'first_name','last_name','id_number','email', 'date', 'birth_date', 'age',
        'city_id', 'parish_id','address', 'motive_request', 'attribute', 'state',
        'institution_name','institution_address', 'rif', 'web',
        'citizen_service_request_type_id', 'type_institution',
        'citizen_service_department_id', 'file_counter', 'date_verification',
        'type_team', 'brand', 'model', 'serial', 'color', 'transfer',
        'inventory_code','entryhour', 'exithour', 'informationteam', 'other',

    ];

    /**
     * Obtiene todos los número telefónicos asociados a la solicitud     *

     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(\App\Models\Phone::class, 'phoneable');
    }

    /**
     * Obtiene todos los documentos asociados a la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }

    /**
     * Obtiene todas las imágenes asociadas a la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }

    /**
     * Método que obtiene la solicitud asociado a un departamento
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceDepartment()
    {
        return $this->belongsTo(CitizenServiceDepartment::class);
    }

    /**
     * Método que obtiene la solicitud asociado a un tipo de solicitud
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceRequestType()
    {
        return $this->belongsTo(CitizenServiceRequestType::class);
    }

    /**
     * Método que obtiene la solicitud asociado a una ciudad
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Método que obtiene la solicitud asociado a una parroquia
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Establece la relación con los indicadores de la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceIndicator()
    {
        return $this->hasMany(CitizenServiceAddIndicator::class, 'request_id', 'id');
    }

    /**
     * Método que obtiene la solicitud asociado a un género
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestGender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    /**
     * Método que obtiene la solicitud asociado a una nacionalidad
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestNationality()
    {
        return $this->belongsTo(PayrollNationality::class, 'nationality_id', 'id');
    }

    /**
     * Método que obtiene la solicitud asociado a un género
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve | xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestDirector()
    {
        return $this->belongsTo(PayrollStaff::class, 'director_id', 'id');
    }

    /**
     * Scope de búsqueda de solicitudes
     *
     * @param Builder $query Objeto con la consulta
     * @param mixed $request Datos de la petición
     *
     * @return Builder
     */
    public function scopeSearch(
        $query,
        $request
    ) {

        $citizen_service_request_types = is_array($request->citizen_service_request_types)
            ? $request->citizen_service_request_types
            : ((json_decode($request->citizen_service_request_types) != '')
                ? json_decode($request->citizen_service_request_types)
                : []);
        $citizen_service_states = is_array($request->citizen_service_states)
            ? $request->citizen_service_states
            : ((json_decode($request->citizen_service_states) != '')
                ? json_decode($request->citizen_service_states)
                : []);
        $start_date = ($request->type_search == 'date') ? null : $request->start_date;
        $end_date = ($request->type_search == 'date') ? null : $request->end_date;
        $date = ($request->type_search == 'period') ? null : $request->date;

        $listRequestTypes = [];
        foreach ($citizen_service_request_types as $field) {
            array_push($listRequestTypes, $field->id ?? $field["id"]);
        }
        $listStates = [];
        foreach ($citizen_service_states as $field) {
            array_push($listStates, $field->id ?? $field["id"]);
        }
        if (isset($start_date) || isset($end_date)) {
            $query->whereBetween("date", [$start_date,$end_date]);
        }
        if (count($listRequestTypes) > 0) {
            $query->whereIn("citizen_service_request_type_id", $listRequestTypes);
        }
        if (count($listStates) > 0) {
            $query->whereIn("state", $listStates);
        }
        if (isset($date)) {
            $query->where("date", $date);
        }


        return $query;
    }

    /**
     * Establece la relación con el código de registro de la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function codeCitizenServiceRegister()
    {
        return $this->belongsTo(CitizenServiceRegister::class);
    }
}
