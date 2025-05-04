<?php

namespace Modules\Asset\Exports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Estate;
use App\Models\Parish;
use App\Models\Country;
use App\Exports\DataExport;
use App\Models\Municipality;
use App\Models\MeasurementUnit;
use Modules\Asset\Models\Asset;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Modules\Asset\Models\AssetUseFunction;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Modules\Asset\Repositories\AssetParametersRepository;

/**
 * @class AssetExport
 * @brief Gestiona la exportación de datos de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetExport extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents,
    WithColumnFormatting,
    WithCustomStartCell
{
    use RegistersEventListeners;

    /**
     * Nombres de los encabezados en las hojas a exportar
     *
     * @var array $namesTitle
     */
    protected $namesTitle;

    /**
     * Tipo de hoja a exportar
     *
     * @var string $type
     */
    protected $type;

    /**
     * Gestiona los parámetros de bienes
     *
     * @var AssetParametersRepository $params
     */
    protected $params;

    /**
     * Definir las columnas a exportar
     *
     * @var array $selects
     */
    protected $selects;

    /**
     * Crea una nueva instancia de la clase.
     *
     * @param string $type
     *
     * @return void
     */
    public function __construct($type = '')
    {
        $this->params = new AssetParametersRepository();
        $this->selects = $this->params->loadAllParameters();
        $this->type = $type;
        $this->namesTitle = [
            'mueble' => [
                'SEDE',
                'ORGANIZACIÓN',
                'PROVEEDOR',
                'UNIDAD ADMINISTRATIVA',
                'CÓDIGO INTERNO DEL BIEN',
                'DESCRIPCIÓN',
                'FORMA ADQUISICIÓN',
                'FECHA ADQUISICIÓN',
                'No DOCUMENTO',
                'VALOR ADQUISICIÓN',
                'MONEDA',
                'ESTADO DEL USO DEL BIEN',
                'CONDICIÓN FÍSICA',
                'SERIAL',
                'MARCA',
                'MODELO',
                'COLOR',
                'CATEGORÍA GENERAL',
                'SUBCATEGORÍA',
                'CATEGORÍA ESPECÍFICA',
                'CÓDIGO',
                'AÑOS DE VIDA ÚTIL',
                'VALOR RESIDUAL'

            ],
            'inmueble' => [
                'SEDE',
                'ORGANIZACIÓN',
                'PROVEEDOR',
                'UNIDAD ADMINISTRATIVA',
                'CÓDIGO INTERNO DEL BIEN',
                'DESCRIPCIÓN',
                'FORMA ADQUISICIÓN',
                'FECHA ADQUISICIÓN',
                'No DOCUMENTO',
                'VALOR ADQUISICIÓN',
                'MONEDA',
                'ESTADO DEL USO DEL BIEN',
                'CONDICIÓN FÍSICA',
                'AÑO DE CONSTRUCCIÓN',
                'EDAD DE CONSTRUCCIÓN',
                'NÚMERO DEL CONTRATO INMUEBLE',
                'RIF COMODATARIO',
                'ESTADO DE OCUPACIÓN',
                'ÁREA DE CONSTRUCCIÓN',
                'UNIDAD DE MEDIDA ÁREA DE CONSTRUCCIÓN',
                'ÁREA DEL TERRENO',
                'UNIDAD MEDIDA ÁREA DEL TERRENO',
                'USO ACTUAL',
                'FECHA INICIO CONTRATO',
                'FECHA FIN CONTRATO',
                'OFICINA DE REGISTRO INMUEBLE',
                'FECHA REGISTRO INMUEBLE',
                'NÚMERO REGISTRO INMUEBLE',
                'TOMO',
                'FOLIO',
                'PAÍS',
                'ESTADO',
                'MUNICIPIO',
                'PARROQUIA',
                'URBANIZACIÓN SECTOR',
                'AVENIDA CALLE',
                'CASA EDIFICIO',
                'PISO',
                'LOCALIZACIÓN',
                'LINDEROS NORTE',
                'LINDEROS SUR',
                'LINDEROS ESTE',
                'LINDEROS OESTE',
                'COORDENADAS DE UBICACIÓN',
                'CATEGORÍA GENERAL',
                'SUBCATEGORÍA',
                'CATEGORÍA ESPECÍFICA',
                'CÓDIGO',
            ],
            'vehiculo' => [
                'SEDE',
                'ORGANIZACIÓN',
                'PROVEEDOR',
                'UNIDAD ADMINISTRATIVA',
                'CÓDIGO INTERNO DEL BIEN',
                'DESCRIPCIÓN',
                'FORMA ADQUISICIÓN',
                'FECHA ADQUISICIÓN',
                'No DOCUMENTO',
                'VALOR ADQUISICIÓN',
                'MONEDA',
                'ESTADO DEL USO DEL BIEN',
                'CONDICIÓN FÍSICA',
                'MARCA',
                'MODELO',
                'COLOR',
                'AÑO FABRICACIÓN',
                'SERIAL CARROCERIA',
                'SERIAL MOTOR',
                'PLACA',
                'CATEGORÍA GENERAL',
                'SUBCATEGORÍA',
                'CATEGORÍA ESPECÍFICA',
                'CÓDIGO',
                'AÑOS DE VIDA ÚTIL',
                'VALOR RESIDUAL'
            ],
            'semoviente' => [
                'SEDE',
                'ORGANIZACIÓN',
                'PROVEEDOR',
                'UNIDAD ADMINISTRATIVA',
                'CÓDIGO INTERNO DEL BIEN',
                'DESCRIPCIÓN',
                'FORMA ADQUISICIÓN',
                'FECHA ADQUISICIÓN',
                'No DOCUMENTO',
                'VALOR ADQUISICIÓN',
                'MONEDA',
                'ESTADO DEL USO DEL BIEN',
                'CONDICIÓN FÍSICA',
                'RAZA',
                'TIPO',
                'PROPÓSITO',
                'PESO',
                'UNIDAD DE MEDIDA',
                'FECHA NACIMIENTO',
                'NÚMERO DE HIERRO',
                'GÉNERO',
                'CATEGORÍA GENERAL',
                'SUBCATEGORÍA',
                'CATEGORÍA ESPECÍFICA',
                'CÓDIGO',
            ],
        ];
    }

    /**
     * Establece el titulo de la hoja en el archivo a exportar
     *
     * @return string Titulo
     */
    public function title(): string
    {
        return 'Registros de ' . $this->type;
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'B4';
    }

    /**
     * Método que define la colección de datos a exportar
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $user = User::without(['roles', 'permissions'])->where('id', auth()->user()->id)->with('profile')->first();

        if (
            $user !== null && !is_null($user->profile) &&
             !is_null($user->profile->institution_id) && $user->profile->institution_id != ""
        ) {
            return Asset::query()
                ->when(
                    ('mueble' === $this->type),
                    fn ($query) => $query->where('asset_type_id', 1)->where('asset_category_id', '!=', 2)->where('asset_category_id', '!=', 8)
                )
                ->when(
                    ('inmueble' === $this->type),
                    fn ($query) => $query->where('asset_type_id', 2)
                )
                ->when(
                    ('vehiculo' === $this->type),
                    fn ($query) => $query->where('asset_category_id', 2)
                )
                ->when(
                    ('semoviente' === $this->type),
                    fn ($query) => $query->where('asset_category_id', 8)
                )
                ->where('institution_id', $user->profile->institution_id)
                ->with(
                    [
                        'assetType',
                        'assetCategory',
                        'assetSubcategory',
                        'assetSpecificCategory',
                        'assetAcquisitionType',
                        'assetCondition',
                        'assetStatus',
                        'assetUseFunction',
                        'institution',
                        'parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country')->get();
                                }])->get();
                            }])->get();
                        },
                    ]
                )
                ->get();
        } else {
            return Asset::query()
                ->when(
                    ('mueble' === $this->type),
                    fn ($query) => $query->where('asset_type_id', 1)
                )
                ->when(
                    ('inmueble' === $this->type),
                    fn ($query) => $query->where('asset_type_id', 2)
                )
                ->when(
                    ('vehiculo' === $this->type),
                    fn ($query) => $query->where('asset_category_id', 2)
                )
                ->when(
                    ('semoviente' === $this->type),
                    fn ($query) => $query->where('asset_category_id', 8)
                )
                ->with(
                    [
                        'assetType',
                        'assetCategory',
                        'assetSubcategory',
                        'assetSpecificCategory',
                        'assetAcquisitionType',
                        'assetCondition',
                        'assetStatus',
                        'assetUseFunction',
                        'institution',
                        'parish' => function ($query) {
                            $query->with(['municipality' => function ($query) {
                                $query->with(['estate' => function ($query) {
                                    $query->with('country')->get();
                                }])->get();
                            }])->get();
                        },
                    ]
                )
                ->get();
        }
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        return $this->namesTitle[$this->type];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     object    $asset    Objeto con las propiedades del modelo a exportar
     *
     * @return    array     Arreglo con los campos estrictamente a ser exportados
     */
    public function map($asset): array
    {
        if ($this->type == 'mueble') {
            return [
                $asset->headquarter->name ?? '',
                $asset->institution->name ?? '',
                !empty($asset->purchaseSupplier) ? ($asset->purchaseSupplier->rif . ' - ' . $asset->purchaseSupplier->name) : '',
                $asset->department->name ?? '',
                $asset->asset_institutional_code ?? '',
                strip_tags($asset->description) ?? '',
                $asset->assetAcquisitionType->name ?? '',
                Date::dateTimeToExcel(Carbon::parse($asset->acquisition_date)),
                $asset->document_num ?? '',
                $asset->acquisition_value ?? '',
                $asset->currency->name ?? '',
                $asset->assetStatus->name ?? '',
                $asset->assetCondition->name ?? '',
                $asset->asset_details['serial'] ?? '',
                $asset->asset_details['brand'] ?? '',
                $asset->asset_details['model'] ?? '',
                $this->getArraysSelect('colors', $asset->asset_details['color_id'] ?? null),
                $asset->assetCategory->name ?? '',
                $asset->assetSubcategory->name ?? '',
                $asset->assetSpecificCategory->name ?? '',
                $asset->code_sigecof ?? '',
                $asset->asset_details['depresciation_years'] ?? '',
                $asset->asset_details['residual_value'] ?? '',
            ];
        } elseif ($this->type == 'inmueble') {
            return [
                $asset->headquarter->name ?? '',
                $asset->institution->name ?? '',
                !empty($asset->purchaseSupplier) ? ($asset->purchaseSupplier->rif . ' - ' . $asset->purchaseSupplier->name) : '',
                $asset->department->name ?? '',
                $asset->asset_institutional_code ?? '',
                strip_tags($asset->description) ?? '',
                $asset->assetAcquisitionType->name ?? '',
                Date::dateTimeToExcel(Carbon::parse($asset->acquisition_date)),
                $asset->document_num ?? '',
                $asset->acquisition_value ?? '',
                $asset->currency->name ?? '',
                $asset->assetStatus->name ?? '',
                $asset->assetCondition->name ?? '',
                $asset->asset_details['construction_year'] ?? '',
                '',
                $asset->asset_details['contract_number'] ?? '',
                $asset->asset_details['rif'] ?? '',
                $this->getArraysSelect('occupancy_status', $asset->asset_details['occupancy_status_id'] ?? null),
                $asset->asset_details['construction_area'] ?? '',
                MeasurementUnit::find($asset->asset_details['construction_measurement_unit_id'] ?? null)?->name ?? '',
                $asset->asset_details['land_area'] ?? '',
                MeasurementUnit::find($asset->asset_details['land_measurement_unit_id'] ?? null)?->name ?? '',
                AssetUseFunction::find($asset->asset_details['asset_use_function_id'] ?? null)?->name ?? '',
                (!empty($asset->asset_details['contract_start_date'] ?? '')) ? Date::dateTimeToExcel(Carbon::parse($asset->asset_details['contract_start_date'])) : '',
                (!empty($asset->asset_details['contract_end_date'] ?? '')) ? Date::dateTimeToExcel(Carbon::parse($asset->asset_details['contract_end_date'])) : '',
                $asset->asset_details['registry_office'] ?? '',
                (!empty($asset->asset_details['registration_date'] ?? '')) ? Date::dateTimeToExcel(Carbon::parse($asset->asset_details['registration_date'])) : '',
                $asset->asset_details['registration_number'] ?? '',
                $asset->asset_details['tome'] ?? '',
                $asset->asset_details['folio'] ?? '',
                Country::find($asset->asset_details['country_id'] ?? null)?->name ?? '',
                Estate::find($asset->asset_details['estate_id'] ?? null)?->name ?? '',
                Municipality::find($asset->asset_details['municipality_id'] ?? null)?->name ?? '',
                Parish::find($asset->asset_details['parish_id'] ?? null)?->name ?? '',
                $asset->asset_details['urbanization_sector'] ?? '',
                $asset->asset_details['avenue_street'] ?? '',
                $asset->asset_details['house'] ?? '',
                $asset->asset_details['floor'] ?? '',
                $asset->asset_details['location'] ?? '',
                $asset->asset_details['north_boundaries'] ?? '',
                $asset->asset_details['south_boundaries'] ?? '',
                $asset->asset_details['east_boundaries'] ?? '',
                $asset->asset_details['west_boundaries'] ?? '',
                $asset->asset_details['location_coordinates'] ?? '',
                $asset->assetCategory->name ?? '',
                $asset->assetSubcategory->name ?? '',
                $asset->assetSpecificCategory->name ?? '',
                $asset->code_sigecof ?? '',
            ];
        } elseif ($this->type == 'vehiculo') {
            return [
                $asset->headquarter->name ?? '',
                $asset->institution->name ?? '',
                !empty($asset->purchaseSupplier) ? ($asset->purchaseSupplier->rif . ' - ' . $asset->purchaseSupplier->name) : '',
                $asset->department->name ?? '',
                $asset->asset_institutional_code ?? '',
                strip_tags($asset->description) ?? '',
                $asset->assetAcquisitionType->name ?? '',
                Date::dateTimeToExcel(Carbon::parse($asset->acquisition_date)),
                $asset->document_num ?? '',
                $asset->acquisition_value ?? '',
                $asset->currency->name ?? '',
                $asset->assetStatus->name ?? '',
                $asset->assetCondition->name ?? '',
                $asset->asset_details['brand'] ?? '',
                $asset->asset_details['model'] ?? '',
                $this->getArraysSelect('colors', $asset->asset_details['color_id'] ?? null),
                $asset->asset_details['manufacture_year'] ?? '',
                $asset->asset_details['bodywork_number'] ?? '',
                $asset->asset_details['engine_number'] ?? '',
                $asset->asset_details['license_plate'] ?? '',
                $asset->assetCategory->name ?? '',
                $asset->assetSubcategory->name ?? '',
                $asset->assetSpecificCategory->name ?? '',
                $asset->code_sigecof ?? '',
                $asset->asset_details['depresciation_years'] ?? '',
                $asset->asset_details['residual_value'] ?? '',
            ];
        } elseif ($this->type == 'semoviente') {
            return [
                $asset->headquarter->name ?? '',
                $asset->institution->name ?? '',
                !empty($asset->purchaseSupplier) ? ($asset->purchaseSupplier->rif . ' - ' . $asset->purchaseSupplier->name) : '',
                $asset->department->name ?? '',
                $asset->asset_institutional_code ?? '',
                strip_tags($asset->description) ?? '',
                $asset->assetAcquisitionType->name ?? '',
                Date::dateTimeToExcel(Carbon::parse($asset->acquisition_date)),
                $asset->document_num ?? '',
                $asset->acquisition_value ?? '',
                $asset->currency->name ?? '',
                $asset->assetStatus->name ?? '',
                $asset->assetCondition->name ?? '',
                $asset->asset_details['race'] ?? '',
                $this->getArraysSelect('cattle_types', $asset->asset_details['type'] ?? null),
                $this->getArraysSelect('purposes', $asset->asset_details['purpose'] ?? null),
                $asset->asset_details['weight'] ?? '',
                MeasurementUnit::find($asset->asset_details['measurement_unit_id'] ?? null)?->name ?? '',
                (!empty($asset->asset_details['date_of_birth'] ?? '')) ? Date::dateTimeToExcel(Carbon::parse($asset->asset_details['date_of_birth'])) : '',
                $asset->asset_details['iron_number'] ?? '',
                $this->getArraysSelect('genders', $asset->asset_details['gender'] ?? null),
                $asset->assetCategory->name ?? '',
                $asset->assetSubcategory->name ?? '',
                $asset->assetSpecificCategory->name ?? '',
                $asset->code_sigecof ?? '',
            ];
        }

        return [];
    }

    /**
     * Establece los formatos de cada columna de la hoja a exportar.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return match ($this->type) {
            'mueble' => [
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_GENERAL,
                'L' => NumberFormat::FORMAT_TEXT,
                'M' => NumberFormat::FORMAT_TEXT,
                'N' => NumberFormat::FORMAT_TEXT,
                'O' => NumberFormat::FORMAT_TEXT,
                'P' => NumberFormat::FORMAT_TEXT,
                'Q' => NumberFormat::FORMAT_TEXT,
                'R' => NumberFormat::FORMAT_TEXT,
                'S' => NumberFormat::FORMAT_TEXT,
                'T' => NumberFormat::FORMAT_TEXT,
                'U' => NumberFormat::FORMAT_TEXT,
                'V' => NumberFormat::FORMAT_TEXT,
                'W' => NumberFormat::FORMAT_NUMBER,
                'X' => NumberFormat::FORMAT_GENERAL,
            ],
            'inmueble' => [
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_GENERAL,
                'L' => NumberFormat::FORMAT_TEXT,
                'M' => NumberFormat::FORMAT_TEXT,
                'N' => NumberFormat::FORMAT_TEXT,
                'O' => NumberFormat::FORMAT_NUMBER,
                'P' => NumberFormat::FORMAT_GENERAL,
                'Q' => NumberFormat::FORMAT_TEXT,
                'R' => NumberFormat::FORMAT_TEXT,
                'S' => NumberFormat::FORMAT_TEXT,
                'T' => NumberFormat::FORMAT_GENERAL,
                'U' => NumberFormat::FORMAT_TEXT,
                'V' => NumberFormat::FORMAT_GENERAL,
                'W' => NumberFormat::FORMAT_TEXT,
                'X' => NumberFormat::FORMAT_TEXT,
                'Y' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'Z' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'AA' => NumberFormat::FORMAT_TEXT,
                'AB' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'AC' => NumberFormat::FORMAT_TEXT,
                'AD' => NumberFormat::FORMAT_TEXT,
                'AE' => NumberFormat::FORMAT_TEXT,
                'AF' => NumberFormat::FORMAT_TEXT,
                'AG' => NumberFormat::FORMAT_TEXT,
                'AH' => NumberFormat::FORMAT_TEXT,
                'AI' => NumberFormat::FORMAT_TEXT,
                'AJ' => NumberFormat::FORMAT_TEXT,
                'AK' => NumberFormat::FORMAT_TEXT,
                'AL' => NumberFormat::FORMAT_TEXT,
                'AM' => NumberFormat::FORMAT_TEXT,
                'AN' => NumberFormat::FORMAT_TEXT,
                'AO' => NumberFormat::FORMAT_TEXT,
                'AP' => NumberFormat::FORMAT_TEXT,
                'AQ' => NumberFormat::FORMAT_TEXT,
                'AR' => NumberFormat::FORMAT_TEXT,
                'AS' => NumberFormat::FORMAT_TEXT,
                'AT' => NumberFormat::FORMAT_TEXT,
                'AU' => NumberFormat::FORMAT_TEXT,
                'AV' => NumberFormat::FORMAT_TEXT,
                'AW' => NumberFormat::FORMAT_TEXT,

            ],
            'vehiculo' => [
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_GENERAL,
                'L' => NumberFormat::FORMAT_TEXT,
                'M' => NumberFormat::FORMAT_TEXT,
                'N' => NumberFormat::FORMAT_TEXT,
                'O' => NumberFormat::FORMAT_TEXT,
                'P' => NumberFormat::FORMAT_TEXT,
                'Q' => NumberFormat::FORMAT_TEXT,
                'R' => NumberFormat::FORMAT_NUMBER,
                'S' => NumberFormat::FORMAT_TEXT,
                'T' => NumberFormat::FORMAT_TEXT,
                'U' => NumberFormat::FORMAT_TEXT,
                'V' => NumberFormat::FORMAT_TEXT,
                'W' => NumberFormat::FORMAT_TEXT,
                'X' => NumberFormat::FORMAT_TEXT,
                'Y' => NumberFormat::FORMAT_TEXT,
                'Z' => NumberFormat::FORMAT_NUMBER,
                'AA' => NumberFormat::FORMAT_GENERAL,
            ],
            'semoviente' => [
                'B' => NumberFormat::FORMAT_TEXT,
                'C' => NumberFormat::FORMAT_TEXT,
                'D' => NumberFormat::FORMAT_TEXT,
                'E' => NumberFormat::FORMAT_TEXT,
                'F' => NumberFormat::FORMAT_TEXT,
                'G' => NumberFormat::FORMAT_TEXT,
                'H' => NumberFormat::FORMAT_TEXT,
                'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'J' => NumberFormat::FORMAT_TEXT,
                'K' => NumberFormat::FORMAT_GENERAL,
                'L' => NumberFormat::FORMAT_TEXT,
                'M' => NumberFormat::FORMAT_TEXT,
                'N' => NumberFormat::FORMAT_TEXT,
                'O' => NumberFormat::FORMAT_TEXT,
                'P' => NumberFormat::FORMAT_TEXT,
                'Q' => NumberFormat::FORMAT_TEXT,
                'R' => NumberFormat::FORMAT_GENERAL,
                'S' => NumberFormat::FORMAT_TEXT,
                'T' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'U' => NumberFormat::FORMAT_TEXT,
                'V' => NumberFormat::FORMAT_TEXT,
                'W' => NumberFormat::FORMAT_TEXT,
                'X' => NumberFormat::FORMAT_TEXT,
                'Y' => NumberFormat::FORMAT_TEXT,
                'Z' => NumberFormat::FORMAT_TEXT,
            ],
        };
    }

    /**
     * Método para registrar eventos de la hoja a exportar
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $letters = match ($this->type) {
            'mueble' => [
                'B' => Protection::PROTECTION_UNPROTECTED,
                'C' => Protection::PROTECTION_UNPROTECTED,
                'D' => Protection::PROTECTION_UNPROTECTED,
                'E' => Protection::PROTECTION_UNPROTECTED,
                'F' => Protection::PROTECTION_UNPROTECTED,
                'G' => Protection::PROTECTION_UNPROTECTED,
                'H' => Protection::PROTECTION_UNPROTECTED,
                'I' => Protection::PROTECTION_UNPROTECTED,
                'J' => Protection::PROTECTION_UNPROTECTED,
                'K' => Protection::PROTECTION_UNPROTECTED,
                'L' => Protection::PROTECTION_UNPROTECTED,
                'M' => Protection::PROTECTION_UNPROTECTED,
                'N' => Protection::PROTECTION_UNPROTECTED,
                'O' => Protection::PROTECTION_UNPROTECTED,
                'P' => Protection::PROTECTION_UNPROTECTED,
                'Q' => Protection::PROTECTION_UNPROTECTED,
                'R' => Protection::PROTECTION_UNPROTECTED,
                'S' => Protection::PROTECTION_UNPROTECTED,
                'T' => Protection::PROTECTION_UNPROTECTED,
                'U' => Protection::PROTECTION_UNPROTECTED,
                'V' => Protection::PROTECTION_PROTECTED,
                'W' => Protection::PROTECTION_UNPROTECTED,
                'X' => Protection::PROTECTION_UNPROTECTED,
            ],
            'inmueble' => [
                'B' => Protection::PROTECTION_UNPROTECTED,
                'C' => Protection::PROTECTION_UNPROTECTED,
                'D' => Protection::PROTECTION_UNPROTECTED,
                'E' => Protection::PROTECTION_UNPROTECTED,
                'F' => Protection::PROTECTION_UNPROTECTED,
                'G' => Protection::PROTECTION_UNPROTECTED,
                'H' => Protection::PROTECTION_UNPROTECTED,
                'I' => Protection::PROTECTION_UNPROTECTED,
                'J' => Protection::PROTECTION_UNPROTECTED,
                'K' => Protection::PROTECTION_UNPROTECTED,
                'L' => Protection::PROTECTION_UNPROTECTED,
                'M' => Protection::PROTECTION_UNPROTECTED,
                'N' => Protection::PROTECTION_UNPROTECTED,
                'O' => Protection::PROTECTION_UNPROTECTED,
                'P' => Protection::PROTECTION_PROTECTED,
                'Q' => Protection::PROTECTION_UNPROTECTED,
                'R' => Protection::PROTECTION_UNPROTECTED,
                'S' => Protection::PROTECTION_UNPROTECTED,
                'T' => Protection::PROTECTION_UNPROTECTED,
                'U' => Protection::PROTECTION_UNPROTECTED,
                'V' => Protection::PROTECTION_UNPROTECTED,
                'W' => Protection::PROTECTION_UNPROTECTED,
                'X' => Protection::PROTECTION_UNPROTECTED,
                'Y' => Protection::PROTECTION_UNPROTECTED,
                'Z' => Protection::PROTECTION_UNPROTECTED,
                'AA' => Protection::PROTECTION_UNPROTECTED,
                'AB' => Protection::PROTECTION_UNPROTECTED,
                'AC' => Protection::PROTECTION_UNPROTECTED,
                'AD' => Protection::PROTECTION_UNPROTECTED,
                'AE' => Protection::PROTECTION_UNPROTECTED,
                'AF' => Protection::PROTECTION_UNPROTECTED,
                'AG' => Protection::PROTECTION_UNPROTECTED,
                'AH' => Protection::PROTECTION_UNPROTECTED,
                'AI' => Protection::PROTECTION_UNPROTECTED,
                'AJ' => Protection::PROTECTION_UNPROTECTED,
                'AK' => Protection::PROTECTION_UNPROTECTED,
                'AL' => Protection::PROTECTION_UNPROTECTED,
                'AM' => Protection::PROTECTION_UNPROTECTED,
                'AN' => Protection::PROTECTION_UNPROTECTED,
                'AO' => Protection::PROTECTION_UNPROTECTED,
                'AP' => Protection::PROTECTION_UNPROTECTED,
                'AQ' => Protection::PROTECTION_UNPROTECTED,
                'AR' => Protection::PROTECTION_UNPROTECTED,
                'AS' => Protection::PROTECTION_UNPROTECTED,
                'AT' => Protection::PROTECTION_UNPROTECTED,
                'AU' => Protection::PROTECTION_UNPROTECTED,
                'AV' => Protection::PROTECTION_UNPROTECTED,
                'AW' => Protection::PROTECTION_PROTECTED,

            ],
            'vehiculo' => [
                'B' => Protection::PROTECTION_UNPROTECTED,
                'C' => Protection::PROTECTION_UNPROTECTED,
                'D' => Protection::PROTECTION_UNPROTECTED,
                'E' => Protection::PROTECTION_UNPROTECTED,
                'F' => Protection::PROTECTION_UNPROTECTED,
                'G' => Protection::PROTECTION_UNPROTECTED,
                'H' => Protection::PROTECTION_UNPROTECTED,
                'I' => Protection::PROTECTION_UNPROTECTED,
                'J' => Protection::PROTECTION_UNPROTECTED,
                'K' => Protection::PROTECTION_UNPROTECTED,
                'L' => Protection::PROTECTION_UNPROTECTED,
                'M' => Protection::PROTECTION_UNPROTECTED,
                'N' => Protection::PROTECTION_UNPROTECTED,
                'O' => Protection::PROTECTION_UNPROTECTED,
                'P' => Protection::PROTECTION_UNPROTECTED,
                'Q' => Protection::PROTECTION_UNPROTECTED,
                'R' => Protection::PROTECTION_UNPROTECTED,
                'S' => Protection::PROTECTION_UNPROTECTED,
                'T' => Protection::PROTECTION_UNPROTECTED,
                'U' => Protection::PROTECTION_UNPROTECTED,
                'V' => Protection::PROTECTION_UNPROTECTED,
                'W' => Protection::PROTECTION_UNPROTECTED,
                'X' => Protection::PROTECTION_UNPROTECTED,
                'Y' => Protection::PROTECTION_PROTECTED,
                'Z' => Protection::PROTECTION_UNPROTECTED,
                'AA' => Protection::PROTECTION_UNPROTECTED,
            ],
            'semoviente' => [
                'B' => Protection::PROTECTION_UNPROTECTED,
                'C' => Protection::PROTECTION_UNPROTECTED,
                'D' => Protection::PROTECTION_UNPROTECTED,
                'E' => Protection::PROTECTION_UNPROTECTED,
                'F' => Protection::PROTECTION_UNPROTECTED,
                'G' => Protection::PROTECTION_UNPROTECTED,
                'H' => Protection::PROTECTION_UNPROTECTED,
                'I' => Protection::PROTECTION_UNPROTECTED,
                'J' => Protection::PROTECTION_UNPROTECTED,
                'K' => Protection::PROTECTION_UNPROTECTED,
                'L' => Protection::PROTECTION_UNPROTECTED,
                'M' => Protection::PROTECTION_UNPROTECTED,
                'N' => Protection::PROTECTION_UNPROTECTED,
                'O' => Protection::PROTECTION_UNPROTECTED,
                'P' => Protection::PROTECTION_UNPROTECTED,
                'Q' => Protection::PROTECTION_UNPROTECTED,
                'R' => Protection::PROTECTION_UNPROTECTED,
                'S' => Protection::PROTECTION_UNPROTECTED,
                'T' => Protection::PROTECTION_UNPROTECTED,
                'U' => Protection::PROTECTION_UNPROTECTED,
                'V' => Protection::PROTECTION_UNPROTECTED,
                'W' => Protection::PROTECTION_UNPROTECTED,
                'X' => Protection::PROTECTION_UNPROTECTED,
                'Y' => Protection::PROTECTION_UNPROTECTED,
                'Z' => Protection::PROTECTION_PROTECTED,
            ],
        };

        $events = [
            AfterSheet::class => function (AfterSheet $event) use ($letters) {
                // Establecer el ancho de las columnas
                if ($this->type == 'vehiculo') {
                    $cellRange = 'B4:AA4';
                    /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                    $validationRangeA = 'validation!$A$2:$A$5000';
                    $validationRangeB = 'validation!$B$2:$B$5000';
                    $validationRangeC = 'validation!$C$2:$C$5000';
                    $validationRangeD = 'validation!$D$2:$D$5000';
                    $validationRangeE = 'validation!$E$2:$E$5000';
                    $validationRangeF = 'validation!$F$2:$F$5000';
                    $validationRangeG = 'validation!$G$2:$G$5000';
                    $validationRangeH = 'validation!$H$2:$H$5000';
                    $validationRangeI = 'validation!$I$2:$I$5000';
                    $validationRangeJ = 'validation!$J$2:$J$5000';
                    $validationRangeK = 'validation!$K$2:$K$5000';
                    $validationRangeL = 'validation!$L$2:$L$5000';

                    $this->setFunctionList($sheet, 'B', 5, null, 'validateA', $validationRangeA);
                    $this->setFunctionList($sheet, 'C', 5, null, 'validateB', $validationRangeB);
                    $this->setFunctionList($sheet, 'D', 5, null, 'validateC', $validationRangeC);
                    $this->setFunctionList($sheet, 'E', 5, null, 'validateD', $validationRangeD);
                    $this->setFunctionList($sheet, 'H', 5, null, 'validateE', $validationRangeE);
                    $this->setFunctionList($sheet, 'L', 5, null, 'validateF', $validationRangeF);
                    $this->setFunctionList($sheet, 'M', 5, null, 'validateG', $validationRangeG);
                    $this->setFunctionList($sheet, 'N', 5, null, 'validateH', $validationRangeH);
                    $this->setFunctionList($sheet, 'Q', 5, null, 'validateI', $validationRangeI);
                    $this->setFunctionList($sheet, 'V', 5, null, 'validateJ', $validationRangeJ);
                    $this->setFunctionList($sheet, 'W', 5, null, 'validateK', $validationRangeK);
                    $this->setFunctionList($sheet, 'X', 5, null, 'validateL', $validationRangeL);
                } elseif ($this->type == 'inmueble') {
                    $cellRange = 'B4:AW4';
                    /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                    $validationRangeA = 'validation!$A$2:$A$5000';
                    $validationRangeB = 'validation!$B$2:$B$5000';
                    $validationRangeC = 'validation!$C$2:$C$5000';
                    $validationRangeD = 'validation!$D$2:$D$5000';
                    $validationRangeE = 'validation!$E$2:$E$5000';
                    $validationRangeF = 'validation!$F$2:$F$5000';
                    $validationRangeG = 'validation!$G$2:$G$5000';
                    $validationRangeH = 'validation!$H$2:$H$5000';
                    $validationRangeI = 'validation!$I$2:$I$5000';
                    $validationRangeJ = 'validation!$J$2:$J$5000';
                    $validationRangeK = 'validation!$K$2:$K$5000';
                    $validationRangeL = 'validation!$L$2:$L$5000';
                    $validationRangeM = 'validation!$M$2:$M$5000';
                    $validationRangeN = 'validation!$N$2:$N$5000';
                    $validationRangeO = 'validation!$O$2:$O$5000';
                    $validationRangeP = 'validation!$P$2:$P$5000';
                    $validationRangeQ = 'validation!$Q$2:$Q$5000';
                    $validationRangeR = 'validation!$R$2:$R$5000';
                    $validationRangeS = 'validation!$S$2:$S$5000';

                    $this->setFunctionList($sheet, 'B', 5, null, 'validateA', $validationRangeA);
                    $this->setFunctionList($sheet, 'C', 5, null, 'validateB', $validationRangeB);
                    $this->setFunctionList($sheet, 'D', 5, null, 'validateC', $validationRangeC);
                    $this->setFunctionList($sheet, 'E', 5, null, 'validateD', $validationRangeD);
                    $this->setFunctionList($sheet, 'H', 5, null, 'validateE', $validationRangeE);
                    $this->setFunctionList($sheet, 'L', 5, null, 'validateF', $validationRangeF);
                    $this->setFunctionList($sheet, 'M', 5, null, 'validateG', $validationRangeG);
                    $this->setFunctionList($sheet, 'N', 5, null, 'validateH', $validationRangeH);
                    $this->setFunctionList($sheet, 'S', 5, null, 'validateI', $validationRangeI);
                    $this->setFunctionList($sheet, 'U', 5, null, 'validateJ', $validationRangeJ);
                    $this->setFunctionList($sheet, 'W', 5, null, 'validateK', $validationRangeK);
                    $this->setFunctionList($sheet, 'X', 5, null, 'validateL', $validationRangeL);
                    $this->setFunctionList($sheet, 'AF', 5, null, 'validateM', $validationRangeM);
                    $this->setFunctionList($sheet, 'AG', 5, null, 'validateN', $validationRangeN);
                    $this->setFunctionList($sheet, 'AH', 5, null, 'validateO', $validationRangeO);
                    $this->setFunctionList($sheet, 'AI', 5, null, 'validateP', $validationRangeP);
                    $this->setFunctionList($sheet, 'AT', 5, null, 'validateQ', $validationRangeQ);
                    $this->setFunctionList($sheet, 'AU', 5, null, 'validateR', $validationRangeR);
                    $this->setFunctionList($sheet, 'AV', 5, null, 'validateS', $validationRangeS);
                } elseif ($this->type == 'mueble') {
                    $cellRange = 'B4:X4';
                    /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                    $validationRangeA = 'validation!$A$2:$A$5000';
                    $validationRangeB = 'validation!$B$2:$B$5000';
                    $validationRangeC = 'validation!$C$2:$C$5000';
                    $validationRangeD = 'validation!$D$2:$D$5000';
                    $validationRangeE = 'validation!$E$2:$E$5000';
                    $validationRangeF = 'validation!$F$2:$F$5000';
                    $validationRangeG = 'validation!$G$2:$G$5000';
                    $validationRangeH = 'validation!$H$2:$H$5000';
                    $validationRangeI = 'validation!$I$2:$I$5000';
                    $validationRangeJ = 'validation!$J$2:$J$5000';
                    $validationRangeK = 'validation!$K$2:$K$5000';
                    $validationRangeL = 'validation!$L$2:$L$5000';

                    $this->setFunctionList($sheet, 'B', 5, null, 'validateA', $validationRangeA);
                    $this->setFunctionList($sheet, 'C', 5, null, 'validateB', $validationRangeB);
                    $this->setFunctionList($sheet, 'D', 5, null, 'validateC', $validationRangeC);
                    $this->setFunctionList($sheet, 'E', 5, null, 'validateD', $validationRangeD);
                    $this->setFunctionList($sheet, 'H', 5, null, 'validateE', $validationRangeE);
                    $this->setFunctionList($sheet, 'L', 5, null, 'validateF', $validationRangeF);
                    $this->setFunctionList($sheet, 'M', 5, null, 'validateG', $validationRangeG);
                    $this->setFunctionList($sheet, 'N', 5, null, 'validateH', $validationRangeH);
                    $this->setFunctionList($sheet, 'R', 5, null, 'validateI', $validationRangeI);
                    $this->setFunctionList($sheet, 'S', 5, null, 'validateJ', $validationRangeJ);
                    $this->setFunctionList($sheet, 'T', 5, null, 'validateK', $validationRangeK);
                    $this->setFunctionList($sheet, 'U', 5, null, 'validateL', $validationRangeL);
                } elseif ($this->type == 'semoviente') {
                    $cellRange = 'B4:Z4';
                    /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                    $validationRangeA = 'validation!$A$2:$A$5000';
                    $validationRangeB = 'validation!$B$2:$B$5000';
                    $validationRangeC = 'validation!$C$2:$C$5000';
                    $validationRangeD = 'validation!$D$2:$D$5000';
                    $validationRangeE = 'validation!$E$2:$E$5000';
                    $validationRangeF = 'validation!$F$2:$F$5000';
                    $validationRangeG = 'validation!$G$2:$G$5000';
                    $validationRangeH = 'validation!$H$2:$H$5000';
                    $validationRangeI = 'validation!$I$2:$I$5000';
                    $validationRangeJ = 'validation!$J$2:$J$5000';
                    $validationRangeK = 'validation!$K$2:$K$5000';
                    $validationRangeL = 'validation!$L$2:$L$5000';
                    $validationRangeM = 'validation!$M$2:$M$5000';
                    $validationRangeN = 'validation!$N$2:$N$5000';
                    $validationRangeO = 'validation!$O$2:$O$5000';

                    $this->setFunctionList($sheet, 'B', 5, null, 'validateA', $validationRangeA);
                    $this->setFunctionList($sheet, 'C', 5, null, 'validateB', $validationRangeB);
                    $this->setFunctionList($sheet, 'D', 5, null, 'validateC', $validationRangeC);
                    $this->setFunctionList($sheet, 'E', 5, null, 'validateD', $validationRangeD);
                    $this->setFunctionList($sheet, 'H', 5, null, 'validateE', $validationRangeE);
                    $this->setFunctionList($sheet, 'L', 5, null, 'validateF', $validationRangeF);
                    $this->setFunctionList($sheet, 'M', 5, null, 'validateG', $validationRangeG);
                    $this->setFunctionList($sheet, 'N', 5, null, 'validateH', $validationRangeH);
                    $this->setFunctionList($sheet, 'P', 5, null, 'validateI', $validationRangeI);
                    $this->setFunctionList($sheet, 'Q', 5, null, 'validateJ', $validationRangeJ);
                    $this->setFunctionList($sheet, 'S', 5, null, 'validateK', $validationRangeK);
                    $this->setFunctionList($sheet, 'V', 5, null, 'validateL', $validationRangeL);
                    $this->setFunctionList($sheet, 'W', 5, null, 'validateM', $validationRangeM);
                    $this->setFunctionList($sheet, 'X', 5, null, 'validateN', $validationRangeN);
                    $this->setFunctionList($sheet, 'Y', 5, null, 'validateO', $validationRangeO);
                }

                /* Definicion de estilos de la cabecera */
                $styleArray = [
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '67A1CF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $sheet->getStyle($cellRange)->applyFromArray($styleArray);
                $sheet->getStyle('I5:I5000')
                    ->getNumberFormat()
                    ->setFormatCode('DD/MM/YYYY');
                if ('inmueble' == $this->type) {
                    for ($row = 5; $row <= 5000; $row++) {
                        $sheet->setCellValue('P' . $row, '=IF(O' . $row . '="", "", YEAR(TODAY()) - O' . $row . ')');
                    };
                    $sheet->getStyle('Y5:Y5000')
                        ->getNumberFormat()
                        ->setFormatCode('DD/MM/YYYY');
                    $sheet->getStyle('Z5:Z5000')
                        ->getNumberFormat()
                        ->setFormatCode('DD/MM/YYYY');
                    $sheet->getStyle('AB5:AB5000')
                        ->getNumberFormat()
                        ->setFormatCode('DD/MM/YYYY');
                } elseif ('semoviente' == $this->type) {
                    $sheet->getStyle('T5:T5000')
                        ->getNumberFormat()
                        ->setFormatCode('DD/MM/YYYY');
                }
                foreach ($letters as $key => $value) {
                    $sheet
                        ->getStyle($key . '5:' . $key . '5000')
                        ->getProtection()->setLocked($value);
                }
                $sheet->getProtection()->setSheet(true);
            },
        ];
        return $events;
    }

    /**
     * Obtiene el valor del arreglo de selección.
     *
     * @param string $type Tipo de información a exportar
     * @param integer $id Identificador del registro a exportar
     *
     * @return string
     */
    public function getArraysSelect(?string $type, ?int $id): ?string
    {
        if (!empty($type)) {
            $list = array_column($this->selects[$type], 'text');
            $data = ((int) $id > 0)
                ? $list[((int) $id)]
                : null;
        }
        return $data ?? '';
    }
}
