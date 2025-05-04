<?php

namespace Modules\Asset\Models;

use App\Models\Country;
use App\Models\Estate;
use App\Models\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Venturecraft\Revisionable\RevisionableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Institution;
use App\Models\MeasurementUnit;
use App\Models\Municipality;
use App\Models\Parish;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Modules\Asset\Repositories\AssetParametersRepository;
use Modules\Purchase\Models\PurchaseSupplier;

/**
 * @class Asset
 * @brief Datos de los bienes institucionales
 *
 * Gestiona el modelo de datos de los bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class Asset extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

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
        'asset_type_id',
        'asset_category_id',
        'asset_subcategory_id',
        'asset_specific_category_id',
        'asset_condition_id',
        'asset_acquisition_type_id',
        'acquisition_date',
        'asset_status_id',
        'acquisition_value',
        'description',
        'institution_id',
        'department_id',
        'purchase_supplier_id',
        'asset_institutional_code',
        'code_sigecof',
        'currency_id',
        'document_num',
        'asset_details',
        'headquarter_id',
        'asset_institution_storages_id'
    ];

    protected $casts = [
        'asset_details' => 'array',
    ];

    protected $appends = [
        'color',
        'country',
        'estate',
        'municipality',
        'parish',
        'occupancy_status',
        'construction_measurement_unit',
        'construction_measurement_unit_acronym',
        'land_measurement_unit',
        'asset_use_function',
        'type',
        'purpose',
        'measurement_unit',
        'gender'
    ];

    /**
     * Método que obtiene el serial de inventario de un bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return string Retorna el serial de inventario de un bien
     */
    public function getCode()
    {
        $date = strtotime($this->acquisition_date);
        $year = date("Y", $date);
        return $this->asset_type_id . '-' . $this->asset_category_id . '-' .
            $this->asset_subcategory_id . '-' . $this->asset_specific_category_id . '-' .
            $year . '-' . $this->id;
    }

    /**
     * Método que obtiene la descripción técnica de un bien institucional
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return string Retorna la descripción técnica de un bien
     */
    public function getDescription()
    {
        $description = 'Código: ' . $this->getCode() . '. ' .
            'Marca: ' . $this->marca . '. ' .
            'Modelo: ' . $this->model;
        if ($this->asset_type_id == 2) {
            $description = $description . '. Serial: ' . $this->serial;
        }
        return $description;
    }

    public function getColorAttribute()
    {
        $params = new AssetParametersRepository();
        $color = [];

        if (array_key_exists('color_id', $this->asset_details)) {
            $color = Arr::first($params->loadColorsData() ?? [], function ($item) {
                return strtolower($item['id']) == $this->asset_details['color_id'];
            });
        }

        return $color['text'] ?? '';
    }

    /**
     * Método que obtiene los nombres de los países
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *@author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getCountryAttribute()
    {
        if (array_key_exists('country_id', $this->asset_details)) {
            return $this->asset_details['country_id']
            ? Country::find($this->asset_details['country_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de los estados
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * *@author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getEstateAttribute()
    {
        if (array_key_exists('estate_id', $this->asset_details)) {
            return $this->asset_details['estate_id']
            ? Estate::find($this->asset_details['estate_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de los municipios
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     */
    public function getMunicipalityAttribute()
    {
        if (array_key_exists('municipality_id', $this->asset_details)) {
            return $this->asset_details['municipality_id']
            ? Municipality::find($this->asset_details['municipality_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de las parroquias
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * *@author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getParishAttribute()
    {
        if (array_key_exists('parish_id', $this->asset_details)) {
            return $this->asset_details['parish_id']
            ? Parish::find($this->asset_details['parish_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de los estados de ocupación
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     */
    public function getOccupancyStatusAttribute()
    {
        $params = new AssetParametersRepository();
        $occupancyStatus = [];

        if (array_key_exists('occupancy_status_id', $this->asset_details)) {
            $occupancyStatus = Arr::first($params->loadOccupancyStatusData() ?? [], function ($item) {
                return strtolower($item['id']) === $this->asset_details['occupancy_status_id'];
            });
        }

        return $occupancyStatus['text'] ?? '';
    }

    /**
     * Método que obtiene los nombres de las unidades de medida en términos de construcción
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getConstructionMeasurementUnitAttribute()
    {
        if (array_key_exists('construction_measurement_unit_id', $this->asset_details)) {
            return $this->asset_details['construction_measurement_unit_id']
            ? MeasurementUnit::find($this->asset_details['construction_measurement_unit_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de las unidades de medida en términos de construcción
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getConstructionMeasurementUnitAcronymAttribute()
    {
        if (array_key_exists('construction_measurement_unit_id', $this->asset_details)) {
            return $this->asset_details['construction_measurement_unit_id']
            ? MeasurementUnit::find($this->asset_details['construction_measurement_unit_id'])['acronym']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de las unidades de medida en términos de terreno
     * @return string
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getLandMeasurementUnitAttribute()
    {
        if (array_key_exists('land_measurement_unit_id', $this->asset_details)) {
            return $this->asset_details['land_measurement_unit_id']
            ? MeasurementUnit::find($this->asset_details['land_measurement_unit_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de las funciones de uso
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getAssetUseFunctionAttribute()
    {
        if (array_key_exists('asset_use_function_id', $this->asset_details)) {
            return $this->asset_details['asset_use_function_id']
            ? AssetUseFunction::find($this->asset_details['asset_use_function_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de los tipos de semovientes
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     */
    public function getTypeAttribute()
    {
        $params = new AssetParametersRepository();
        $cattleType = [];

        if (array_key_exists('type', $this->asset_details)) {
            $cattleType = Arr::first($params->loadCattleTypesData() ?? [], function ($item) {
                return strtolower($item['id']) === $this->asset_details['type'];
            });
        }

        return $cattleType['text'] ?? '';
    }

    /**
     * Método que obtiene los nombres de los propósitos
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     */
    public function getPurposeAttribute()
    {
        $params = new AssetParametersRepository();
        $purpose = [];

        if (array_key_exists('purpose', $this->asset_details)) {
            $purpose = Arr::first($params->loadPurposesData() ?? [], function ($item) {
                return strtolower($item['id']) === $this->asset_details['purpose'];
            });
        }

        return $purpose['text'] ?? '';
    }

    /**
     * Método que obtiene los nombres de las unidades de medida para los semovientes
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     */
    public function getMeasurementUnitAttribute()
    {
        if (array_key_exists('measurement_unit_id', $this->asset_details)) {
            return $this->asset_details['measurement_unit_id']
            ? MeasurementUnit::find($this->asset_details['measurement_unit_id'])['name']
            : '';
        }
        return '';
    }

    /**
     * Método que obtiene los nombres de los géneros para los semovientes
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     */
    public function getGenderAttribute()
    {
        $params = new AssetParametersRepository();
        $assetGender = [];

        if (array_key_exists('gender', $this->asset_details)) {
            $assetGender = Arr::first($params->loadGendersData() ?? [], function ($item) {
                return strtolower($item['id']) === $this->asset_details['gender'];
            });
        }

        return $assetGender['text'] ?? '';
    }

    /**
     * Método que obtiene el tipo al que pertenece el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo AssetType
     */
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function headquarter()
    {
        return $this->belongsTo(\App\Models\Headquarter::class);
    }
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    /**
     * Método que obtiene la categoria a la que pertenece el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetCategory
     */
    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Método que obtiene la subcategoria a la que pertenece el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetSubcategory
     */
    public function assetSubcategory()
    {
        return $this->belongsTo(AssetSubcategory::class);
    }

    /**
     * Método que obtiene la categoria específica a la que pertenece el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetSpecificCategory
     */

    public function assetSpecificCategory()
    {
        return $this->belongsTo(AssetSpecificCategory::class);
    }

    /**
     * Método que obtiene el tipo de adquisicion del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetAcquisitionType
     */
    public function assetAcquisitionType()
    {
        return $this->belongsTo(AssetAcquisitionType::class);
    }

    /**
     * Método que obtiene la condición física del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetCondition
     */
    public function assetCondition()
    {
        return $this->belongsTo(AssetCondition::class);
    }

    /**
     * Método que obtiene el estatus de uso del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetStatus
     */
    public function assetStatus()
    {
        return $this->belongsTo(AssetStatus::class);
    }

    /**
     * Método que obtiene la función de uso del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetUseFunction
     */
    public function assetUseFunction()
    {
        return $this->belongsTo(AssetUseFunction::class);
    }

    /**
     * Get the purchaseSupplier that owns the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplier(): BelongsTo
    {
        return $this->belongsTo(PurchaseSupplier::class);
    }

    /**
     * Método que obtiene los bienes asignados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasOne Objeto con el registro relacionado al modelo
     * AssetAsignationAsset
     */

    public function assetAsignationAsset()
    {
        return $this->hasOne(AssetAsignationAsset::class);
    }

    /**
     * Método que obtiene los bienes desincorporados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasOne Objeto con el registro relacionado al modelo
     * AssetDisincorporationAsset
     */
    public function assetDisincorporationAsset()
    {
        return $this->hasOne(AssetDisincorporationAsset::class);
    }

    /**
     * Método que obtiene los bienes desincorporados
     *
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * AssetDepreciationAsset
     */
    public function assetDepreciationAsset()
    {
        return $this->hasMany(AssetDepreciationAsset::class);
    }
    /**
     * Método que obtiene el valor en libro de un bien
     *
     * @author Manuel Zambrano <mazambrano@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * AssetDepreciationAsset
     */
    public function assetBook()
    {
        return $this->hasMany(AssetBook::class);
    }
    /**
     * Método que obtiene los bienes solicitados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasOne Objeto con el registro relacionado al modelo
     * AssetRequestAsset
     */
    public function assetRequestAsset()
    {
        return $this->hasOne(AssetRequestAsset::class);
    }

    /**
     * Método que obtiene los bienes inventariados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * AssetInventoryAsset
     */
    public function assetInventoryAssets()
    {
        return $this->hasMany(AssetInventoryAsset::class);
    }

    /**
     * Método que obtiene la parroquia donde esta ubicado el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo Parish
     */
    public function parish()
    {
        return $this->belongsTo(\App\Models\Parish::class);
    }

    /**
     * Método que obtiene la moneda en que se expresa el valor del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo Currency
     */
    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * Método que obtiene la institución a la que pertenece el bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     *                                                           Institution
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene los ajustes relacionados al bien
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     *                                                          AssetAdjustmentAsset
     */
    public function assetAdjustmentAssets()
    {
        return $this->hasMany(AssetAdjustmentAsset::class);
    }

    /**
     * Método que obtiene los valores según libro relacionados al bien
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     *                                                         AssetBook
     */
    public function assetBooks()
    {
        return $this->hasMany(AssetBook::class);
    }

    /**
     * Método que obtiene los valores de las depreciaciones asociadas a un bien
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     *                                                         AssetDepreciationAsset
     */
    public function assetDepreciationAssets()
    {
        return $this->hasMany(AssetDepreciationAsset::class);
    }

    /**
     * Método que obtiene los bienes registrados filtrados por su clasificación
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @param  [string] $type           Tipo del bien
     * @param  [string] $category       Categoria general del bien
     * @param  [string] $subcategory    Subcategoria del bien
     * @param  [string] $specific       Categoria específica del bien
     * @return [Object]             Objeto con los bienes registrados
     */
    public function scopeCodeClasification($query, $type, $category, $subcategory, $specific, $is_dis, $ids)
    {
        if ($is_dis == false) {
            if ($type != "") {
                if ($category != "") {
                    if ($subcategory != "") {
                        if ($specific != "") {
                            return $query->where('asset_type_id', $type)
                                ->where('asset_category_id', $category)
                                ->where('asset_subcategory_id', $subcategory)
                                ->where('asset_specific_category_id', $specific);
                        }
                        return $query->where('asset_type_id', $type)
                            ->where('asset_category_id', $category)
                            ->where('asset_subcategory_id', $subcategory);
                    }
                    return $query->where('asset_type_id', $type)
                        ->where('asset_category_id', $category);
                }
                return $query->where('asset_type_id', $type);
            } else {
                return $query;
            }
        } else {
            if ($type != "") {
                if ($category != "") {
                    if ($subcategory != "") {
                        if ($specific != "") {
                            return $query->where('asset_type_id', $type)
                                ->where('asset_category_id', $category)
                                ->where('asset_subcategory_id', $subcategory)
                                ->where('asset_specific_category_id', $specific)
                                ->whereNotIn('id', $ids)
                                ->where('asset_status_id', '!=', 1)
                                ->where('asset_status_id', '!=', 3)
                                ->where('asset_status_id', '!=', 6)
                                ->where('asset_status_id', '!=', 11);
                        }
                        return $query->where('asset_type_id', $type)
                            ->where('asset_category_id', $category)
                            ->where('asset_subcategory_id', $subcategory)
                            ->whereNotIn('id', $ids)
                            ->where('asset_status_id', '!=', 1)
                            ->where('asset_status_id', '!=', 3)
                            ->where('asset_status_id', '!=', 6)
                            ->where('asset_status_id', '!=', 11);
                    }
                    return $query->where('asset_type_id', $type)
                        ->where('asset_category_id', $category)
                        ->whereNotIn('id', $ids)
                        ->where('asset_status_id', '!=', 1)
                        ->where('asset_status_id', '!=', 3)
                        ->where('asset_status_id', '!=', 6)
                        ->where('asset_status_id', '!=', 11);
                }
                return $query->where('asset_type_id', $type)
                    ->whereNotIn('id', $ids)
                    ->where('asset_status_id', '!=', 1)
                    ->where('asset_status_id', '!=', 3)
                    ->where('asset_status_id', '!=', 6)
                    ->where('asset_status_id', '!=', 11);
            } else {
                return $query->whereNotIn('id', $ids)
                    ->where('asset_status_id', '!=', 1)
                    ->where('asset_status_id', '!=', 3)
                    ->where('asset_status_id', '!=', 6)
                    ->where('asset_status_id', '!=', 11);
            }
        }
    }

    /**
     * Método que obtiene los bienes registrados filtrados por la fecha de registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @param  [timestamp] $start   Fecha de inicio de busqueda
     * @param  [timestamp] $end     Fecha de fin de busqueda
     * @param  [string] $mes        Mes de busqueda
     * @param  [year] $year         Año de busqueda
     * @return [Object]             Objeto con los bienes registrados
     */
    public function scopeDateClasification($query, $start, $end, $mes, $year)
    {
        if (!is_null($start)) {
            if (!is_null($end)) {
                return $query->whereBetween("created_at", [$start, $end]);
            } else {
                return $query->whereBetween("created_at", [$start, now()]);
            }
        }

        if (!is_null($mes)) {
            if (!is_null($year)) {
                return $query->whereMonth('created_at', $mes)
                    ->whereYear('created_at', $year);
            } else {
                return $query->whereMonth('created_at', $mes);
            }
        }
    }

    /**
     * Método que obtiene los bienes registrados filtrados por la ubicación dentro de la institución
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @param  [Integer] $institution   Identificador único de la institución
     * @param  [Integer] $department    Identificador único del departamento o dependencia
     * @return [Object]                 Objeto con los bienes registrados
     */
    public function scopeDependenceClasification($query, $institution, $department)
    {
        /**
         * Falta asociar con la institución
         * Ojo: Debe ser los equipos que ya han sido asignados
         */
    }

    /**
     * Método que obtiene los bienes registrados filtrados por la ubicación dentro de la institución
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @param  [String] $search   Cadena de texto con el que se va filtrar la data
     * @return [Object]           Objeto con los bienes registrados
     */
    public function scopeSearchAsignation($query, $search)
    {
        return $query->when('' != $search, function ($query) use ($search) {
            return $query->where('asset_institutional_code', 'like', '%' . $search . '%')
                ->orWhereHas('assetSpecificCategory', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                })
                ->orWhereHas('assetCondition', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                })
                ->orWhereHas('assetStatus', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                });
        });
    }

    public function scopeSearchRegisters($query, $search)
    {
        return $query->when('' != $search, function ($query) use ($search) {
            return $query->where('code_sigecof', 'like', '%' . $search . '%')
                ->orWhere('asset_institutional_code', 'like', '%' . $search . '%')
                ->orWhereHas('assetSpecificCategory', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                })
                ->orWhereHas('assetCondition', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                })
                ->orWhereHas('assetStatus', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                });
        });
    }
}
