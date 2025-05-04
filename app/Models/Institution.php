<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\Asset\Models\AssetBuilding;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Istitution
 * @brief Datos de Organizaciones
 *
 * Gestiona el modelo de datos para las Organizaciones
 *
 * @property  string|integer  $id
 * @property  string  $onapre_code
 * @property  string  $rif
 * @property  string  $acronym
 * @property  string  $name
 * @property  string  $business_name
 * @property  string  $start_operations_date
 * @property  string  $legal_base
 * @property  string  $legal_form
 * @property  string  $main_activity
 * @property  string  $mission
 * @property  string  $vision
 * @property  string  $legal_address
 * @property  string  $web
 * @property  string  $composition_assets
 * @property  string  $postal_code
 * @property  boolean $active
 * @property  boolean $default
 * @property  boolean $retention_agent
 * @property  int     $institution_sector_id
 * @property  int     $institution_type_id
 * @property  int     $municipality_id
 * @property  int     $city_id
 * @property  int     $logo_id
 * @property  int     $banner_id
 * @property  Image   $logo
 * @property  Image   $banner
 * @property  FiscalYear $fiscalYears
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Institution extends Model implements Auditable
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
        'onapre_code', 'rif', 'acronym', 'name', 'business_name', 'start_operations_date', 'legal_base',
        'legal_form', 'main_activity', 'mission', 'vision', 'legal_address', 'web', 'composition_assets',
        'postal_code', 'active', 'default', 'retention_agent', 'institution_sector_id',
        'institution_type_id', 'municipality_id', 'city_id', 'logo_id', 'banner_id'
    ];

    /**
     * Ejecuta acciones generales del modelo
     *
     * @return void
     */
    protected static function booted()
    {
        // Scope global para consultar la institución a la que pertenece el usuario
        static::addGlobalScope('user_institution', function (Builder $builder) {
            // Objeto con información del usuario autenticado
            $user = auth()->user();
            if (
                $user !== null && method_exists($user, 'profile') && !is_null($user->profile) &&
                property_exists($user->profile, 'institution')
            ) {
                $builder->where('id', $user->profile->institution->id)->withTrashed();
            }
        });
    }

    /**
     * Método que obtiene el logotipo de la Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logo()
    {
        return $this->belongsTo(Image::class, 'logo_id');
    }

    /**
     * Método que obtiene el banner de la Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function banner()
    {
        return $this->belongsTo(Image::class, 'banner_id');
    }

    /**
     * Método que obtiene el sector de la Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sector()
    {
        return $this->belongsTo(InstitutionSector::class);
    }

    /**
     * Método que obtiene el tipo de la Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(InstitutionType::class);
    }

    /**
     * Obtiene el municipio al que pertenece una Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Obtiene la ciudad a la que pertenece una Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Método que obtiene los departamentos asociados a la intitución.
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Método que obtiene los perfiles de usuarios asociados a una Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Método que obtiene los años fiscales asociados a una Organización
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fiscalYears()
    {
        return $this->hasMany(FiscalYear::class);
    }

    /**
     * Método que obtiene las asignacion relacionadas con la institución
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetAsignation()
    {
        return $this->hasMany(\Modules\Asset\Models\AssetAsignation::class);
    }

    /**
     * Método que obtiene los edificios a la institucion
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(AssetBuilding::class);
    }
}
