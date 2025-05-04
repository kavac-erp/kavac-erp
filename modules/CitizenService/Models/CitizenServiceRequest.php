<?php

namespace Modules\CitizenService\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Support\Arr;

/**
 * @class CitizenService
 * @brief Datos de información de ingresar solicitud
 *
 * Gestiona el modelo de ingresar solicitud
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class CitizenServiceRequest extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $with = ['city', 'parish'];
    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'code','first_name','last_name','id_number','email', 'date', 'birth_date', 'age',
        'city_id', 'parish_id','address', 'motive_request', 'attribute', 'state',
        'institution_name','institution_address', 'rif', 'web',
        'citizen_service_request_type_id', 'type_institution', 'citizen_service_department_id', 'file_counter', 'date_verification',

        'type_team', 'brand', 'model', 'serial', 'color', 'transfer',
        'inventory_code','entryhour', 'exithour', 'informationteam', 'other',

    ];

    /**
     * Obtiene todos los número telefónicos asociados a la solicitud
     *

     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(\App\Models\Phone::class, 'phoneable');
    }

    /**
     * Obtiene todos los número telefónicos asociados a la solicitud
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }

    /**
     * Obtiene todos los número telefónicos asociados a la solicitud
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citizenServiceRequestType()
    {
        return $this->belongsTo(CitizenServiceRequestType::class);
    }

    /**
     * Método que obtiene la solicitud asociado a una ciudad
     *
     * @author
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    /**
     * Método que obtiene la solicitud asociado a una parroquia
     *
     * @author
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function citizenServiceIndicator()
    {
        return $this->hasMany(CitizenServiceAddIndicator::class, 'request_id', 'id');
    }

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

    public function codeCitizenServiceRegister()
    {
        return $this->belongsTo(CitizenServiceRegister::class);
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    // public function getDate()
    // {
    //     return $this->date;
    // }
}
