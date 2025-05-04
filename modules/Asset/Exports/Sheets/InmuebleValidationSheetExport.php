<?php

namespace Modules\Asset\Exports\Sheets;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Estate;
use App\Models\Headquarter;
use App\Models\Institution;
use App\Models\MeasurementUnit;
use App\Models\Municipality;
use App\Models\Parish;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Asset\Models\AssetAcquisitionType;
use Modules\Asset\Models\AssetCategory;
use Modules\Asset\Models\AssetCondition;
use Modules\Asset\Models\AssetSpecificCategory;
use Modules\Asset\Models\AssetStatus;
use Modules\Asset\Models\AssetSubcategory;
use Modules\Asset\Models\AssetUseFunction;
use Modules\Asset\Repositories\AssetParametersRepository;
use Modules\Purchase\Models\PurchaseSupplier;

/**
 * @class InmuebleValidationSheetExport
 * @brief Gestiona la exportación de datos de inmuebles en el módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InmuebleValidationSheetExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    /**
     * Gestiona los parámetros de bienes
     *
     * @var AssetParametersRepository $params
     */
    protected $params;

    /**
     * Metodo que define el nombre de la hoja de inmuebles
     *
     * @return string
     */
    public function title(): string
    {
        return 'validation';
    }

    /**
     * Metodo que define la colección de datos a exportar
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $this->params = new AssetParametersRepository();
        $headquarters = Headquarter::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $institutions = Institution::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $purchaseSuppliers = PurchaseSupplier::query()
            ->selectRaw("CONCAT(rif, ' - ', name) as full_name")
            ->get()
            ->toBase()
            ->pluck('full_name')
            ->toArray();
        $departments = Department::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $assetAcquisitionTypes = AssetAcquisitionType::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $currencies = Currency::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $statuses = AssetStatus::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $conditions = AssetCondition::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $occupancies = array_column(array_filter($this->params->loadOccupancyStatusData(), function ($item) {
            return $item['id'] !== '';
        }), 'text');
        $constructionMeasurementUnits =
        $landMeasurementUnits = MeasurementUnit::query()->select('name')->get()->toBase()->pluck('name')->toArray();

        $useFunctions = AssetUseFunction::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $countries = Country::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $states = Estate::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $municipalities = Municipality::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $parishes = Parish::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $categories = AssetCategory::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $subCategories = AssetSubcategory::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $specificCategories = AssetSpecificCategory::query()->select('name')->get()->toBase()->pluck('name')->toArray();
        $maxCount = max(
            count($headquarters),
            count($institutions),
            count($purchaseSuppliers),
            count($departments),
            count($assetAcquisitionTypes),
            count($currencies),
            count($statuses),
            count($conditions),
            count($occupancies),
            count($constructionMeasurementUnits),
            count($landMeasurementUnits),
            count($useFunctions),
            count($countries),
            count($states),
            count($municipalities),
            count($parishes),
            count($categories),
            count($subCategories),
            count($specificCategories),
        );

        $headquarters = array_pad($headquarters, $maxCount, '');
        $institutions = array_pad($institutions, $maxCount, '');
        $purchaseSuppliers = array_pad($purchaseSuppliers, $maxCount, '');
        $departments = array_pad($departments, $maxCount, '');
        $assetAcquisitionTypes = array_pad($assetAcquisitionTypes, $maxCount, '');
        $currencies = array_pad($currencies, $maxCount, '');
        $statuses = array_pad($statuses, $maxCount, '');
        $conditions = array_pad($conditions, $maxCount, '');
        $occupancies = array_pad($occupancies, $maxCount, '');
        $constructionMeasurementUnits = array_pad($constructionMeasurementUnits, $maxCount, '');
        $landMeasurementUnits = array_pad($landMeasurementUnits, $maxCount, '');
        $useFunctions = array_pad($useFunctions, $maxCount, '');
        $countries = array_pad($countries, $maxCount, '');
        $states = array_pad($states, $maxCount, '');
        $municipalities = array_pad($municipalities, $maxCount, '');
        $parishes = array_pad($parishes, $maxCount, '');
        $categories = array_pad($categories, $maxCount, '');
        $subCategories = array_pad($subCategories, $maxCount, '');
        $specificCategories = array_pad($specificCategories, $maxCount, '');

        return collect(array_map(
            null,
            $headquarters,
            $institutions,
            $purchaseSuppliers,
            $departments,
            $assetAcquisitionTypes,
            $currencies,
            $statuses,
            $conditions,
            $occupancies,
            $constructionMeasurementUnits,
            $landMeasurementUnits,
            $useFunctions,
            $countries,
            $states,
            $municipalities,
            $parishes,
            $categories,
            $subCategories,
            $specificCategories,
        ));
    }

    /**
     * Obtiene los encabezados de la hoja de inmuebles.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Sede',
            'Organización',
            'Proveedor',
            'Unidad administrativa',
            'Tipo de adquisición',
            'Moneda',
            'Estatus de uso',
            'Condición física',
            'Estado de ocupación',
            'Unidad de medida area de construccion',
            'Unidad de medida area de terreno',
            'Uso',
            'Pais',
            'Estado',
            'Municipio',
            'Parroquia',
            'Categoría',
            'Subcategoría',
            'Categoría específica'
        ];
    }

    /**
     * Metodo para registrar eventos de la hoja de inmuebles.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        /* @todo Instrucciones para ocultar la hoja de validaciones
         * Descomentar cuando este verificada la hoja
         */
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                $worksheet->setSheetState('hidden');
            },
        ];
    }
}
