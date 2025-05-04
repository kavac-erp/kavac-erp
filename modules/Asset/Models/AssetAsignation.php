<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Module;
use App\Models\User;
use App\Traits\ModelsTrait;
use Carbon\Carbon;

/**
 * @class AssetAsignation
 * @brief Datos de las asignaciones de los bienes institucionales
 *
 * Gestiona el modelo de datos de las asignaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
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
        'formed_by_id', 'delivered_by_id'
    ];

    /**
     * Método que obtiene los bienes asignados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * AssetAsignationAsset
     */
    public function assetAsignationAssets()
    {
        return $this->hasMany(AssetAsignationAsset::class);
    }

    /**
     * Método que obtiene el trabajador al que se le asigna el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return Array|\Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * PayrollStaff
     */
    public function payrollStaff()
    {
        return (Module::has('Payroll'))
               ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) : [];
    }

    /**
     * Método que obtiene las solicitude de entrega de los bienes asignados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * AssetAsignationDelivery
     */
    public function assetAsignationDelivery()
    {
        return $this->hasMany(AssetAsignationDelivery::class);
    }
    /**
     * Método que obtiene el departamento donde recide el bien asignado
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return Objeto con el registro relacionado al modelo Department
     */
    public function department()
    {
        return (Module::has('Department')) ?
             $this->belongsTo(\App\Models\Department::class) : [];
    }

    /**
     * Método que obtiene el usuario asociado al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Método que obtiene la institución a la cual está relaciona la asiganción
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     *                                                           Institution
     */
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class);
    }

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
