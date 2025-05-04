<?php

namespace Modules\Asset\Imports;

use DateTime;
use App\Models\Parish;
use App\Models\Currency;
use App\Models\Headquarter;
use App\Models\Institution;
use Illuminate\Support\Arr;
use App\Models\Municipality;
use App\Models\MeasurementUnit;
use Modules\Asset\Models\Asset;
use Modules\Asset\Models\Estate;
use Modules\Asset\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Asset\Models\AssetBook;
use Modules\Asset\Models\AssetStatus;
use Modules\Payroll\Models\Department;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Asset\Models\AssetCategory;
use Modules\Asset\Models\AssetCondition;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Modules\Asset\Models\AssetSubcategory;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Modules\Purchase\Models\PurchaseSupplier;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Asset\Models\AssetAcquisitionType;
use Modules\Asset\Models\AssetSpecificCategory;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Modules\Asset\Repositories\AssetParametersRepository;

/**
 * @class AssetImport
 * @brief Gestiona la importación de datos de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetImport implements
    ToModel,
    WithValidation,
    WithHeadingRow,
    WithEvents,
    SkipsOnFailure,
    SkipsEmptyRows
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    /**
     * Parámetros para la importación de datos
     *
     * @var AssetParametersRepository $params
     */
    protected AssetParametersRepository $params;

    /**
     * Define los selectores para la importación de datos
     *
     * @var array $selects
     */
    protected array $selects;

    /**
     * Método constructor de la clase
     *
     * @param string $type Tipo de importación
     * @param string $fileErrosPath Ruta donde se guardan los errores
     *
     * @return void
     */
    public function __construct(
        protected string $type,
        protected string $fileErrosPath,
    ) {
        $this->params = new AssetParametersRepository();
        $this->selects = $this->params->loadAllParameters();
    }

    /**
     * Define las columnas de la cabecera del archivo
     *
     * @return int
     */
    public function headingRow(): int
    {
        HeadingRowFormatter::default('none');

        return 4;
    }

    /**
     * Define el modelo de la importación
     *
     * @param array $row Fila de los datos a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        /* Contiene el identificador de la categoría asociada al bien */
        $assetCategory = AssetCategory::query()
            ->where('name', $row['CATEGORÍA GENERAL'])
            ->first() ?? null;

        /* Contiene el identificador del tipo de bien asociado al bien */
        $assetTypeId = $assetCategory?->assetType?->id;

        /* Contiene el identificador de la sub-categoría asociada al bien */
        $assetSubCategory = AssetSubCategory::query()
            ->where('name', $row['SUBCATEGORÍA'])
            ->first() ?? null;

        /* Contiene el identificador de la categoría específica asociada al bien */
        $assetSpecificCategory = AssetSpecificCategory::query()
            ->where('name', $row['CATEGORÍA ESPECÍFICA'])
            ->first() ?? null;

        /* Contiene el código asociada al bien */
        $code = $assetCategory->code . $assetSubCategory->code . $assetSpecificCategory->code;

        /* Contiene el identificador de la sede asociada al bien */
        $headquarterId = Headquarter::query()
            ->where('name', $row['SEDE'])
            ->value('id');

        /* Contiene el identificador de la organización asociada al bien */
        $institutionId = Institution::query()
            ->where('name', $row['ORGANIZACIÓN'])
            ->orWhere(
                function ($query) {
                    $query
                        ->where('active', true)
                        ->where('default', true);
                }
            )
            ->value('id');

        /* Contiene un array con los datos (rif y nombre) del proveedor asociado al bien */
        $supplier = explode(' - ', $row['PROVEEDOR']);

        if (count($supplier) == 2) {
            $purchaseSupplierId = PurchaseSupplier::query()
                ->where('rif', $supplier[0])
                ->where('name', $supplier[1])
                ->value('id');
        }

        /* Contiene el identificador de la unidad administrativa asociada al bien */
        $departmentId = Department::query()
            ->where('name', $row['UNIDAD ADMINISTRATIVA'] ?? null)
            ->value('id');

        /* Contiene el identificador de la forma de adquisición asociada al bien */
        $assetAcquisitionTypeId = AssetAcquisitionType::query()
            ->where('name', $row['FORMA ADQUISICIÓN'])
            ->value('id');

        /* Contiene el identificador de la moneda asociada al bien */
        $currencyId = Currency::query()
            ->where('name', $row['MONEDA'])
            ->value('id');

        /* Contiene el identificador del estado de uso asociada al bien */
        $assetStatusId = AssetStatus::query()
            ->where('name', $row['ESTADO DEL USO DEL BIEN'])
            ->value('id');

        /* Contiene el identificador de la condición física asociada al bien */
        $assetConditionId = AssetCondition::query()
            ->where('name', $row['CONDICIÓN FÍSICA'])
            ->value('id');

        /* Contiene el identificador del color asociado al bien */
        $colorId = $this->getArraysSelect('colors', $row['COLOR'] ?? null);

        if ('inmueble' === $this->type) {
            /* Contiene el identificador de estatus de ocupación de un bien */
            $occupancyStatusId = $this->getArraysSelect('occupancy_status', $row['ESTADO DE OCUPACIÓN'] ?? null);

            /* Contiene el identificador de la unidad de medida del area de construcción */
            $constructionMeasurementUnitId = MeasurementUnit::query()
                ->where('name', $row['UNIDAD DE MEDIDA ÁREA DE CONSTRUCCIÓN'] ?? null)
                ->value('id');

            /* Contiene el identificador de la unidad de medida del area del terreno */
            $landMeasurementUnitId = MeasurementUnit::query()
                ->where('name', $row['UNIDAD MEDIDA ÁREA DEL TERRENO'] ?? null)
                ->value('id');

            $assetUseFunctionId = $this->getArraysSelect('use_functions', $row['USO ACTUAL'] ?? null);

            $countryId = Country::query()
                ->where('name', $row['PAÍS'])
                ->value('id');

            $estateId = Estate::query()
                ->where('name', $row['ESTADO'])
                ->value('id');

            $municipalityId = Municipality::query()
                ->where('name', $row['MUNICIPIO'])
                ->value('id');

            $parishId = Parish::query()
                ->where('name', $row['PARROQUIA'])
                ->value('id');

            /* Datos de los bienes a importar */
            $data = [
                'asset_type_id' => $assetTypeId,
                'asset_category_id' => $assetCategory?->id,
                'asset_subcategory_id' => $assetSubCategory?->id,
                'asset_specific_category_id' => $assetSpecificCategory?->id,
                'asset_condition_id' => $assetConditionId,
                'asset_acquisition_type_id' => $assetAcquisitionTypeId,
                'acquisition_date' => !empty($row['FECHA ADQUISICIÓN']) ? new DateTime($row['FECHA ADQUISICIÓN']) : null,
                'asset_status_id' => $assetStatusId,
                'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                'description' => $row['DESCRIPCIÓN'],
                'institution_id' => $institutionId,
                'department_id' => $departmentId,
                'asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN'],
                'code_sigecof' => $code,
                'currency_id' => $currencyId,
                'document_num' => $row['No DOCUMENTO'],
                'purchase_supplier_id' => $purchaseSupplierId ?? null,
                'asset_details' => [
                    'code' => $row['CÓDIGO INTERNO DEL BIEN'],
                    'asset_condition_id' => $assetConditionId,
                    'asset_status_id' => $assetStatusId,
                    'department_id' => $departmentId,
                    'description' => $row['DESCRIPCIÓN'],
                    'headquarter_id' => $headquarterId,
                    'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                    'construction_year' => $row['AÑO DE CONSTRUCCIÓN'],
                    'construction_age' => $row['EDAD DE CONSTRUCCIÓN'],
                    'contract_number' => $row['NÚMERO DEL CONTRATO INMUEBLE'],
                    'rif' => !empty($row['RIF COMODATARIO']) ? $row['RIF COMODATARIO'] : 'N/P',
                    'occupancy_status_id' => $occupancyStatusId,
                    'construction_area' => $row['ÁREA DE CONSTRUCCIÓN'],
                    'construction_measurement_unit_id' => $constructionMeasurementUnitId,
                    'land_area' => $row['ÁREA DEL TERRENO'],
                    'land_measurement_unit_id' => $landMeasurementUnitId,
                    'asset_use_function_id' => $assetUseFunctionId,
                    'contract_start_date' => !empty($row['FECHA INICIO CONTRATO']) ? $row['FECHA INICIO CONTRATO'] : null,
                    'contract_end_date' => !empty($row['FECHA FIN CONTRATO']) ? $row['FECHA FIN CONTRATO'] : null,
                    'registry_office' => $row['OFICINA DE REGISTRO INMUEBLE'],
                    'registration_date' => $row['FECHA REGISTRO INMUEBLE'],
                    'registration_number' => $row['NÚMERO REGISTRO INMUEBLE'],
                    'tome' => $row['TOMO'],
                    'folio' => $row['FOLIO'],
                    'country_id' => $countryId,
                    'estate_id' => $estateId,
                    'municipality_id' => $municipalityId,
                    'parish_id' => $parishId,
                    'urbanization_sector' => $row['URBANIZACIÓN SECTOR'],
                    'avenue_street' => $row['AVENIDA CALLE'],
                    'house' => $row['CASA EDIFICIO'],
                    'floor' => $row['PISO'],
                    'location' => $row['LOCALIZACIÓN'],
                    'north_boundaries' => $row['LINDEROS NORTE'],
                    'south_boundaries' => $row['LINDEROS SUR'],
                    'east_boundaries' => $row['LINDEROS ESTE'],
                    'west_boundaries' => $row['LINDEROS OESTE'],
                    'location_coordinates' => $row['COORDENADAS DE UBICACIÓN'],
                ],
                'headquarter_id' => $headquarterId,
                'deleted_at' => null,
            ];
        } elseif ('mueble' === $this->type) {
            /* Datos de los bienes a importar */
            $data = [
                'asset_type_id' => $assetTypeId,
                'asset_category_id' => $assetCategory?->id,
                'asset_subcategory_id' => $assetSubCategory?->id,
                'asset_specific_category_id' => $assetSpecificCategory?->id,
                'asset_condition_id' => $assetConditionId,
                'asset_acquisition_type_id' => $assetAcquisitionTypeId,
                'acquisition_date' => !empty($row['FECHA ADQUISICIÓN']) ? new DateTime($row['FECHA ADQUISICIÓN']) : null,
                'asset_status_id' => $assetStatusId,
                'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                'description' => $row['DESCRIPCIÓN'],
                'institution_id' => $institutionId,
                'department_id' => $departmentId,
                'asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN'],
                'code_sigecof' => $code,
                'currency_id' => $currencyId,
                'document_num' => $row['No DOCUMENTO'],
                'purchase_supplier_id' => $purchaseSupplierId ?? null,
                'asset_details' => [
                    'code' => $row['CÓDIGO INTERNO DEL BIEN'],
                    'asset_condition_id' => $assetConditionId,
                    'asset_status_id' => $assetStatusId,
                    'department_id' => $departmentId,
                    'description' => $row['DESCRIPCIÓN'],
                    'headquarter_id' => $headquarterId,
                    'serial' => $row['SERIAL'],
                    'brand' => $row['MARCA'],
                    'model' => $row['MODELO'],
                    'color_id' => $colorId,
                    'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                    'residual_value' => $row['VALOR RESIDUAL'],
                    'depresciation_years' => $row['AÑOS DE VIDA ÚTIL'],
                ],
                'headquarter_id' => $headquarterId,
                'deleted_at' => null,
            ];
        } elseif ('vehiculo' === $this->type) {
            /* Datos de los bienes a importar */
            $data = [
                'asset_type_id' => $assetTypeId,
                'asset_category_id' => $assetCategory?->id,
                'asset_subcategory_id' => $assetSubCategory?->id,
                'asset_specific_category_id' => $assetSpecificCategory?->id,
                'asset_condition_id' => $assetConditionId,
                'asset_acquisition_type_id' => $assetAcquisitionTypeId,
                'acquisition_date' => !empty($row['FECHA ADQUISICIÓN']) ? new DateTime($row['FECHA ADQUISICIÓN']) : null,
                'asset_status_id' => $assetStatusId,
                'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                'description' => $row['DESCRIPCIÓN'],
                'institution_id' => $institutionId,
                'department_id' => $departmentId,
                'asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN'],
                'code_sigecof' => $code,
                'currency_id' => $currencyId,
                'document_num' => $row['No DOCUMENTO'],
                'purchase_supplier_id' => $purchaseSupplierId ?? null,
                'asset_details' => [
                    'code' => $row['CÓDIGO INTERNO DEL BIEN'],
                    'asset_condition_id' => $assetConditionId,
                    'asset_status_id' => $assetStatusId,
                    'department_id' => $departmentId ?? null,
                    'description' => $row['DESCRIPCIÓN'],
                    'headquarter_id' => $headquarterId,
                    'brand' => $row['MARCA'],
                    'model' => $row['MODELO'],
                    'color_id' => $colorId,
                    'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                    'residual_value' => $row['VALOR RESIDUAL'],
                    'depresciation_years' => $row['AÑOS DE VIDA ÚTIL'],
                    'manufacture_year' => $row['AÑO FABRICACIÓN'],
                    'bodywork_number' => $row['SERIAL CARROCERIA'],
                    'engine_number' => $row['SERIAL MOTOR'],
                    'license_plate' => $row['PLACA']
                ],
                'headquarter_id' => $headquarterId,
                'deleted_at' => null,
            ];
        } elseif ('semoviente' === $this->type) {
            /* Contiene el identificador del tipo de semoviente */
            $typeId = $this->getArraysSelect('cattle_types', $row['TIPO'] ?? null);

            /* Contiene el identificador del tipo de semoviente */
            $purposeId = $this->getArraysSelect('purposes', $row['PROPÓSITO'] ?? null);

            /* Contiene el identificador de la unidad de medida del peso del semoviente */
            $measurementUnitId = MeasurementUnit::query()
                ->where('name', $row['UNIDAD DE MEDIDA'] ?? null)
                ->value('id');

            /* Contiene el identificador del género de semoviente */
            $genderId = $this->getArraysSelect('genders', $row['GÉNERO'] ?? null);

            /* Datos de los bienes a importar */
            $data = [
                'asset_type_id' => $assetTypeId,
                'asset_category_id' => $assetCategory?->id,
                'asset_subcategory_id' => $assetSubCategory?->id,
                'asset_specific_category_id' => $assetSpecificCategory?->id,
                'asset_condition_id' => $assetConditionId,
                'asset_acquisition_type_id' => $assetAcquisitionTypeId,
                'acquisition_date' => !empty($row['FECHA ADQUISICIÓN']) ? new DateTime($row['FECHA ADQUISICIÓN']) : null,
                'asset_status_id' => $assetStatusId,
                'acquisition_value' => $row['VALOR ADQUISICIÓN'],
                'description' => $row['DESCRIPCIÓN'],
                'institution_id' => $institutionId,
                'department_id' => $departmentId,
                'asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN'],
                'code_sigecof' => $code,
                'currency_id' => $currencyId,
                'document_num' => $row['No DOCUMENTO'],
                'purchase_supplier_id' => $purchaseSupplierId ?? null,
                'asset_details' => [
                    'code' => $row['CÓDIGO INTERNO DEL BIEN'],
                    'asset_condition_id' => $assetConditionId,
                    'asset_status_id' => $assetStatusId,
                    'department_id' => $departmentId,
                    'description' => $row['DESCRIPCIÓN'],
                    'headquarter_id' => $headquarterId,
                    'race' => $row['RAZA'],
                    'type' => $typeId,
                    'purpose' => $purposeId,
                    'weight' => $row['PESO'],
                    'measurement_unit_id' => $measurementUnitId,
                    'date_of_birth' => $row['FECHA NACIMIENTO'],
                    'gender' => $genderId,
                    'iron_number' => $row['NÚMERO DE HIERRO'],
                    'acquisition_value' => $row['VALOR ADQUISICIÓN']
                ],
                'headquarter_id' => $headquarterId,
                'deleted_at' => null,
            ];
        }

        $asset = Asset::withTrashed()->updateOrCreate(
            ['asset_institutional_code' => $row['CÓDIGO INTERNO DEL BIEN']],
            $data
        );

        $assetBook = $asset->assetBook()->latest()->first();

        AssetBook::updateOrCreate([
            'id' => $assetBook?->id
        ], [
            'asset_id' => $asset->id,
            'amount' => $row['VALOR ADQUISICIÓN']
        ]);

        return $asset;
    }

    /**
     * Prepara los datos para la validación
     *
     * @param array $data Arreglo con los datos a validar
     * @param integer $index Índice del arreglo
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        $assets = DB::table('assets')->get(['asset_details', 'asset_institutional_code', 'document_num'])
            ->map(fn ($item) => array_merge(
                json_decode($item->asset_details, true),
                [
                    'document_num' => $item->document_num,
                    'asset_institutional_code' => $item->asset_institutional_code,
                ]
            ));

        $assetExistCodeData = $assets->filter(fn ($item) => $data['CÓDIGO INTERNO DEL BIEN'] == $item['asset_institutional_code']);

        $assetExistCode = (empty($data['CÓDIGO']))
            ? (count($assetExistCodeData) > 0)
            : (count($assetExistCodeData) > 1);

        if ($assetExistCode) {
            $data['CÓDIGO INTERNO DEL BIEN'] = true;
        }

        if ('semoviente' != $this->type && 'mueble' != $this->type) {
            $assetExistNumDocData = $assets->first(fn ($item) => (($data['No DOCUMENTO'] == $item['document_num']) && ($data['CÓDIGO INTERNO DEL BIEN'] != $item['asset_institutional_code'])));
            if (!empty($assetExistNumDocData)) {
                $data['No DOCUMENTO'] = true;
            }
        }

        try {
            $data['FECHA ADQUISICIÓN'] = (!empty($data['FECHA ADQUISICIÓN'])) ? Date::excelToDateTimeObject($data['FECHA ADQUISICIÓN'])->format('Y-m-d') : '';
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $data['FECHA ADQUISICIÓN'] = false;
        }

        if ($this->type == 'inmueble') {
            $assetContractNumberData = $assets->first(fn ($item) => (($data['NÚMERO DEL CONTRATO INMUEBLE'] == ($item['contract_number'] ?? null)) && ($data['CÓDIGO INTERNO DEL BIEN'] != $item['asset_institutional_code'])));
            $assetRegistrationNumberData = $assets->first(fn ($item) => (($data['NÚMERO REGISTRO INMUEBLE'] == ($item['registration_number'] ?? null)) && ($data['CÓDIGO INTERNO DEL BIEN'] != $item['asset_institutional_code'])));

            if (!empty($assetContractNumberData)) {
                $data['NÚMERO DEL CONTRATO INMUEBLE'] = true;
            }
            if (!empty($assetRegistrationNumberData)) {
                $data['NÚMERO REGISTRO INMUEBLE'] = true;
            }
            try {
                $data['FECHA INICIO CONTRATO'] = (!empty($data['FECHA INICIO CONTRATO'])) ? Date::excelToDateTimeObject($data['FECHA INICIO CONTRATO'])->format('Y-m-d') : '';
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                $data['FECHA INICIO CONTRATO'] = false;
            }
            try {
                $data['FECHA FIN CONTRATO'] = (!empty($data['FECHA FIN CONTRATO'])) ? Date::excelToDateTimeObject($data['FECHA FIN CONTRATO'])->format('Y-m-d') : '';
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                $data['FECHA FIN CONTRATO'] = false;
            }
            try {
                $data['FECHA REGISTRO INMUEBLE'] = (!empty($data['FECHA REGISTRO INMUEBLE'])) ? Date::excelToDateTimeObject($data['FECHA REGISTRO INMUEBLE'])->format('Y-m-d') : '';
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                $data['FECHA REGISTRO INMUEBLE'] = false;
            }
        } elseif ($this->type == 'vehiculo') {
            $assetBodyworkNumber = $assets->first(fn ($item) => (($data['SERIAL CARROCERIA'] == ($item['bodywork_number'] ?? null)) && ($data['CÓDIGO INTERNO DEL BIEN'] != $item['asset_institutional_code'])));
            $assetEngineNumber = $assets->first(fn ($item) => (($data['SERIAL MOTOR'] == ($item['engine_number'] ?? null)) && ($data['CÓDIGO INTERNO DEL BIEN'] != $item['asset_institutional_code'])));
            $assetLicensePlate = $assets->first(fn ($item) => (($data['PLACA'] == ($item['license_plate'] ?? null)) && ($data['CÓDIGO INTERNO DEL BIEN'] != $item['asset_institutional_code'])));

            if (!empty($assetBodyworkNumber)) {
                $data['SERIAL CARROCERIA'] = true;
            }
            if (!empty($assetEngineNumber)) {
                $data['SERIAL MOTOR'] = true;
            }
            if (!empty($assetLicensePlate)) {
                $data['PLACA'] = true;
            }
        } elseif ($this->type == 'semoviente') {
            try {
                $data['FECHA NACIMIENTO'] = (!empty($data['FECHA NACIMIENTO'])) ? Date::excelToDateTimeObject($data['FECHA NACIMIENTO'])->format('Y-m-d') : '';
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                $data['FECHA NACIMIENTO'] = false;
            }

            $assetIronNumber = $assets->first(fn ($item) => (($data['NÚMERO DE HIERRO'] == ($item['iron_number'] ?? null)) && ($item['CÓDIGO INTERNO DEL BIEN'] != $data['CÓDIGO INTERNO DEL BIEN'])));
            if (!empty($assetIronNumber)) {
                $data['NÚMERO DE HIERRO'] = true;
            }
        }

        return $data;
    }

    /**
     * Reglas de validación sobre los datos a importar
     *
     * @return array
     */
    public function rules(): array
    {
        if ($this->type == 'mueble') {
            return [
                'SEDE' => ['required'],
                'ORGANIZACIÓN' => ['required'],
                'UNIDAD ADMINISTRATIVA' => ['required'],
                'CÓDIGO INTERNO DEL BIEN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo CÓDIGO INTERNO DEL BIEN ya ha sido registrado.');
                    }
                }],
                'FORMA ADQUISICIÓN' => ['required'],
                'FECHA ADQUISICIÓN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                        }
                    }
                }],
                'CATEGORÍA GENERAL' => ['required', 'exists:asset_categories,name'],
                'SUBCATEGORÍA' => ['required', 'exists:asset_subcategories,name'],
                'CATEGORÍA ESPECÍFICA' => ['required', 'exists:asset_specific_categories,name'],
                'ESTADO DEL USO DEL BIEN' => ['required'],
                'CONDICIÓN FÍSICA' => ['required'],
                'PROVEEDOR' => ['nullable'],
                'DESCRIPCIÓN' => ['nullable'],
                'No DOCUMENTO' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo No DOCUMENTO ya ha sido registrado.');
                    }
                }],
                'VALOR ADQUISICIÓN' => ['required'],
                'MONEDA' => ['nullable'],
                'SERIAL' => ['required'],
                'MARCA' => ['required'],
                'MODELO' => ['required'],
                'COLOR' => ['required'],
                'AÑOS DE VIDA ÚTIL' => ['nullable'],
                'VALOR RESIDUAL' => ['nullable']
            ];
        } elseif ($this->type == 'inmueble') {
            return [
                'SEDE' => ['required'],
                'ORGANIZACIÓN' => ['required'],
                'PROVEEDOR' => ['nullable'],
                'UNIDAD ADMINISTRATIVA' => ['required'],
                'CÓDIGO INTERNO DEL BIEN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo CÓDIGO INTERNO DEL BIEN ya ha sido registrado.');
                    }
                }],
                'DESCRIPCIÓN' => ['nullable'],
                'FORMA ADQUISICIÓN' => ['required'],
                'FECHA ADQUISICIÓN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                        }
                    }
                }],
                'No DOCUMENTO' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo No DOCUMENTO ya ha sido registrado.');
                    }
                }],
                'VALOR ADQUISICIÓN' => ['required'],
                'MONEDA' => ['nullable'],
                'ESTADO DEL USO DEL BIEN' => ['required'],
                'CONDICIÓN FÍSICA' => ['required'],
                'AÑO DE CONSTRUCCIÓN' => ['required'],
                'EDAD DE CONSTRUCCIÓN' => ['required'],
                'NÚMERO DEL CONTRATO INMUEBLE' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo NÚMERO DEL CONTRATO INMUEBLE ya ha sido registrado.');
                    }
                }],
                'RIF COMODATARIO' => ['nullable'],
                'ESTADO DE OCUPACIÓN' => ['required'],
                'ÁREA DE CONSTRUCCIÓN' => ['required'],
                'UNIDAD DE MEDIDA ÁREA DE CONSTRUCCIÓN' => ['required'],
                'ÁREA DEL TERRENO' => ['required'],
                'UNIDAD MEDIDA ÁREA DEL TERRENO' => ['required'],
                'USO ACTUAL' => ['required'],
                'FECHA INICIO CONTRATO' => ['nullable', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA INICIO CONTRATO no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA INICIO CONTRATO no tiene un formato de fecha válido.');
                        }
                    }
                }, 'after_or_equal:FECHA FIN CONTRATO', 'after_or_equal:FECHA ADQUISICIÓN'],
                'FECHA FIN CONTRATO' => ['nullable', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA FIN CONTRATO no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA FIN CONTRATO no tiene un formato de fecha válido.');
                        }
                    }
                }, 'after_or_equal:FECHA INICIO CONTRATO'],
                'OFICINA DE REGISTRO INMUEBLE' => ['required'],
                'FECHA REGISTRO INMUEBLE' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA REGISTRO INMUEBLE no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA REGISTRO INMUEBLE no tiene un formato de fecha válido.');
                        }
                    }
                }, 'after_or_equal:FECHA REGISTRO INMUEBLE'],
                'NÚMERO REGISTRO INMUEBLE' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo NÚMERO REGISTRO INMUEBLE ya ha sido registrado.');
                    }
                }],
                'TOMO' => ['required'],
                'FOLIO' => ['required'],
                'PAÍS' => ['required'],
                'ESTADO' => ['required'],
                'MUNICIPIO' => ['required'],
                'PARROQUIA' => ['required'],
                'URBANIZACIÓN SECTOR' => ['required'],
                'AVENIDA CALLE' => ['required'],
                'CASA EDIFICIO' => ['required'],
                'PISO' => ['required'],
                'LOCALIZACIÓN' => ['required'],
                'LINDEROS NORTE' => ['required'],
                'LINDEROS SUR' => ['required'],
                'LINDEROS ESTE' => ['required'],
                'LINDEROS OESTE' => ['required'],
                'COORDENADAS DE UBICACIÓN' => ['required'],
                'CATEGORÍA GENERAL' => ['required', 'exists:asset_categories,name'],
                'SUBCATEGORÍA' => ['required', 'exists:asset_subcategories,name'],
                'CATEGORÍA ESPECÍFICA' => ['required', 'exists:asset_specific_categories,name'],
            ];
        } elseif ($this->type == 'vehiculo') {
            return [
                'SEDE' => ['required'],
                'ORGANIZACIÓN' => ['required'],
                'PROVEEDOR' => ['nullable'],
                'UNIDAD ADMINISTRATIVA' => ['required'],
                'CÓDIGO INTERNO DEL BIEN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo CÓDIGO INTERNO DEL BIEN ya ha sido registrado.');
                    }
                }],
                'DESCRIPCIÓN' => ['nullable'],
                'FORMA ADQUISICIÓN' => ['required'],
                'FECHA ADQUISICIÓN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                        }
                    }
                }],
                'No DOCUMENTO' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo No DOCUMENTO ya ha sido registrado.');
                    }
                }],
                'VALOR ADQUISICIÓN' => ['required'],
                'MONEDA' => ['nullable'],
                'ESTADO DEL USO DEL BIEN' => ['required'],
                'CONDICIÓN FÍSICA' => ['required'],
                'MARCA' => ['required'],
                'MODELO' => ['required'],
                'COLOR' => ['required'],
                'AÑO FABRICACIÓN' => ['required'],
                'SERIAL CARROCERIA' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo SERIAL DE CARROCERIA del bien ya ha sido registrado.');
                    }
                }],
                'SERIAL MOTOR' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo SERIAL MOTOR del bien ya ha sido registrado.');
                    }
                }],
                'PLACA' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo PLACA del bien ya ha sido registrado.');
                    }
                }],
                'CATEGORÍA GENERAL' => ['required'],
                'SUBCATEGORÍA' => ['required'],
                'CATEGORÍA ESPECÍFICA' => ['required'],
                'AÑOS DE VIDA ÚTIL' => ['nullable'],
                'VALOR RESIDUAL' => ['nullable']
            ];
        } elseif ($this->type == 'semoviente') {
            return [
                'SEDE' => ['required'],
                'ORGANIZACIÓN' => ['required'],
                'PROVEEDOR' => ['nullable'],
                'UNIDAD ADMINISTRATIVA' => ['required'],
                'CÓDIGO INTERNO DEL BIEN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo CÓDIGO INTERNO DEL BIEN ya ha sido registrado.');
                    }
                }],
                'DESCRIPCIÓN' => ['nullable'],
                'FORMA ADQUISICIÓN' => ['required'],
                'FECHA ADQUISICIÓN' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA ADQUISICIÓN no tiene un formato de fecha válido.');
                        }
                    }
                }],
                'No DOCUMENTO' => ['required'],
                'VALOR ADQUISICIÓN' => ['required'],
                'MONEDA' => ['nullable'],
                'ESTADO DEL USO DEL BIEN' => ['required'],
                'CONDICIÓN FÍSICA' => ['required'],
                'RAZA' => ['required'],
                'TIPO' => ['required'],
                'PROPÓSITO' => ['required'],
                'PESO' => ['required'],
                'UNIDAD DE MEDIDA' => ['required'],
                'FECHA NACIMIENTO' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo FECHA NACIMIENTO no tiene un formato de fecha válido.');
                    } else {
                        $format = 'Y-m-d';
                        $d = DateTime::createFromFormat($format, $value);

                        if (!$d || $d->format($format) !== $value) {
                            $onFailure('El campo FECHA NACIMIENTO no tiene un formato de fecha válido.');
                        }
                    }
                }],
                'NÚMERO DE HIERRO' => ['required', function ($attribute, $value, $onFailure) {
                    if (is_bool($value)) {
                        $onFailure('El campo NÚMERO DE HIERRO ya ha sido registrado.');
                    }
                }],
                'GÉNERO' => ['required'],
                'CATEGORÍA GENERAL' => ['required', 'exists:asset_categories,name'],
                'SUBCATEGORÍA' => ['required', 'exists:asset_subcategories,name'],
                'CATEGORÍA ESPECÍFICA' => ['required', 'exists:asset_specific_categories,name']
            ];
        }

        return [];
    }

    /**
     * Obtiene la lista de valores de un select
     *
     * @param string $type Tipo de información
     * @param string $name Texto en el select
     *
     * @return boolean|integer|null
     */
    public function getArraysSelect(?string $type, ?string $name): ?int
    {
        if (!empty($type) && !empty($name)) {
            $list = Arr::first(
                $this->selects[$type],
                function ($item) use ($name) {
                    return strtolower($item['text']) === strtolower($name);
                }
            );

            if ($list !== null) {
                return $list['id'];
            }
        }
        return null;
    }

    /**
     * Acciones a ejecutar ante un error en la importación de datos
     *
     * @param \Maatwebsite\Excel\Validators\Failure[]|object $failures
     *
     * @return void
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $validationErrors = [
                'row' => $failure->row(),
                'attribute' => str_replace('_value', '', $failure->attribute()),
                'error' => $failure->errors()[0],
                'sheetName' => 'Registro de ' . $this->type,
            ];
            $jsonErrors = json_encode($validationErrors);
            Storage::disk('temporary')->append($this->fileErrosPath, $jsonErrors);
        }
    }

    /**
     * Registro de eventos al importar datos
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                if ('inmueble' === $this->type) {
                    $worksheet = $event->getDelegate()->getDelegate();
                    $highestRow = $worksheet->getHighestDataRow();

                    for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
                        $cell = $worksheet->getCell('P' . $rowIndex);

                        if ($cell->isFormula()) {
                            $value = (int) $cell->getCalculatedValue();
                            if ($value !== 0) {
                                $cell->setValue($value);
                            } else {
                                $worksheet->removeRow($rowIndex, 1);
                                $rowIndex--;
                            }
                        }
                    }
                }
            },
        ];
    }
}
