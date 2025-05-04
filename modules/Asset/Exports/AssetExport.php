<?php

namespace Modules\Asset\Exports;

use App\Models\Country;
use App\Models\Estate;
use App\Models\Gender;
use App\Models\MeasurementUnit;
use App\Models\Municipality;
use App\Models\Parish;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Modules\Asset\Repositories\AssetParametersRepository;
use Modules\Asset\Models\Asset;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Asset\Models\AssetUseFunction;

class AssetExport extends \App\Exports\DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents,
    WithCustomStartCell
{
    use RegistersEventListeners;

    protected $namesTitle;
    protected $type;
    protected $params;
    protected $selects;

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
                'No. DOCUMENTO',
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
                'No. DOCUMENTO',
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
                'URBANIZACIÓN/SECTOR',
                'AVENIDA/CALLE',
                'CASA/EDIFICIO',
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
                'No. DOCUMENTO',
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
                'No. DOCUMENTO',
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
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $auth = auth()->user();

        $user = User::where('id', auth()->user()->id)->with('profile')->first();

        if (
            $user !== null && !is_null($user->profile) &&
             !is_null($user->profile->institution_id) && $user->profile->institution_id != ""
        ) {
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
     * @method    headings
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
     * @method    map
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
                Carbon::createFromFormat('Y-m-d', $asset->acquisition_date)->format('d-m-Y'),
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
                Carbon::createFromFormat('Y-m-d', $asset->acquisition_date)->format('d-m-Y'),
                $asset->document_num ?? '',
                $asset->acquisition_value ?? '',
                $asset->currency->name ?? '',
                $asset->assetStatus->name ?? '',
                $asset->assetCondition->name ?? '',
                $asset->asset_details['construction_year'] ?? '',
                $asset->asset_details['construction_age'] ?? '',
                $asset->asset_details['contract_number'] ?? '',
                $asset->asset_details['rif'] ?? '',
                $this->getArraysSelect('occupancy_status', $asset->asset_details['occupancy_status_id'] ?? null),
                $asset->asset_details['construction_area'] ?? '',
                MeasurementUnit::find($asset->asset_details['construction_measurement_unit_id'] ?? null)?->name ?? '',
                $asset->asset_details['land_area'] ?? '',
                MeasurementUnit::find($asset->asset_details['land_measurement_unit_id'] ?? null)?->name ?? '',
                AssetUseFunction::find($asset->asset_details['asset_use_function_id'] ?? null)?->name ?? '',
                $asset->asset_details['contract_start_date'] ?? '',
                $asset->asset_details['contract_end_date'] ?? '',
                $asset->asset_details['registry_office'] ?? '',
                $asset->asset_details['Fecha de registro del inmueble'] ?? '',
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
                Carbon::createFromFormat('Y-m-d', $asset->acquisition_date)->format('d-m-Y'),
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
                Carbon::createFromFormat('Y-m-d', $asset->acquisition_date)->format('d-m-Y'),
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
                $asset->asset_details['date_of_birth'] ?? '',
                $asset->asset_details['iron_number'] ?? '',
                Gender::find($asset->asset_details['gender'] ?? null)?->name ?? '',
                $asset->assetCategory->name ?? '',
                $asset->assetSubcategory->name ?? '',
                $asset->assetSpecificCategory->name ?? '',
                $asset->code_sigecof ?? '',
            ];
        }
    }

    public function registerEvents(): array
    {
        $events = [
            AfterSheet::class => function (AfterSheet $event) {
                // Establecer el ancho de las columnas
                if ($this->type == 'vehiculo') {
                    $cellRange = 'B4:AA4';
                    /** Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /** Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
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
                    /** Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /** Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
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
                    /** Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /** Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
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
                    /** Se crea una instancia Worksheet para acceder a las dos sheet. */
                    $sheet = $event->sheet->getDelegate();
                    /** Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
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

                /** Definicion de estilos de la cabecera */
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
            },
        ];
        return $events;
    }

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
