<?php

namespace Modules\Asset\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Department;
use App\Models\Institution;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class AssetAsignation
 * @brief Datos de las asignaciones de los bienes institucionales
 *
 * Gestiona el modelo de datos de las asignaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAsignation extends Model implements Auditable
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
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code', 'payroll_staff_id', 'department_id', 'user_id', 'institution_id',
        'location_place', 'state', 'ids_assets', 'authorized_by_id',
        'formed_by_id', 'delivered_by_id',
        'building_id',
        'floor_id',
        'section_id',
    ];

    /**
     * Método que obtiene los bienes asignados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetAsignationAssets()
    {
        return $this->hasMany(AssetAsignationAsset::class);
    }

    /**
     * Método que obtiene el trabajador al que se le asigna el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) : [];
    }

    /**
     * Método que obtiene las solicitude de entrega de los bienes asignados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetAsignationDelivery()
    {
        return $this->hasMany(AssetAsignationDelivery::class);
    }
    /**
     * Método que obtiene el departamento donde recide el bien asignado
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Método que obtiene el usuario asociado al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Método que obtiene la institución a la cual está relaciona la asiganción
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene la edificación asociada a cada asignación
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(AssetBuilding::class, 'building_id');
    }

    /**
     * Método que obtiene el nivel asociado a cada asignación
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(AssetFloor::class, 'floor_id');
    }

    /**
     * Método que obtiene la sección asociada a cada asignación
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(AssetSection::class, 'section_id');
    }

    /**
     * Filtro de búsqueda
     *
     * @param Builder $query Objeto con la consulta
     * @param string $search Datos a buscar
     *
     * @return Builder
     */
    public function scopeSearch($query, $search)
    {
        $isDate = true;
        $formattedDate = '';
        try {
            $formattedDate = Carbon::createFromFormat('d/m/Y', $search)?->format('Y-m-d');
        } catch (\Throwable $th) {
            $isDate = false;
        }

        return $query->when('' != $search, function ($query) use ($search, $formattedDate, $isDate) {
            return $query
                ->where(function ($query) use ($search, $formattedDate, $isDate) {
                    $query->when($isDate, function ($query) use ($formattedDate) {
                        return $query->whereDate('created_at', $formattedDate);
                    });
                })
                ->orWhereRaw('LOWER(code) LIKE ?', [strtolower("%$search%")])
                ->orWhereRaw('LOWER(location_place) LIKE ?', [strtolower("%$search%")])
                ->orWhereRaw('LOWER(state) LIKE ?', [strtolower("%$search%")])
                ->orWhereHas('payrollStaff', function ($query) use ($search) {
                    $query
                        ->whereRaw('LOWER(last_name) LIKE ?', [strtolower("%$search%")])
                        ->orWhereRaw('LOWER(first_name) LIKE ?', [strtolower("%$search%")]);
                });
        });
    }
}
