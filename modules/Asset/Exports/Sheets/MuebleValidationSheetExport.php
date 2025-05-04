<?php

namespace Modules\Asset\Exports\Sheets;

use App\Models\Currency;
use App\Models\Department;
use App\Models\Headquarter;
use App\Models\Institution;
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
use Modules\Asset\Repositories\AssetParametersRepository;
use Modules\Purchase\Models\PurchaseSupplier;

class MuebleValidationSheetExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    protected $params;

    public function title(): string
    {
        return 'validation';
    }

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
        $colors = array_column(array_filter($this->params->loadColorsData(), function ($item) {
            return $item['id'] !== '';
        }), 'text');
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
            count($colors),
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
        $colors = array_pad($colors, $maxCount, '');
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
            $colors,
            $categories,
            $subCategories,
            $specificCategories,
        ));
    }

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
            'Color',
            'Categoría',
            'Subcategoría',
            'Categoría específica'
        ];
    }

    public function registerEvents(): array
    {
        /** @todo Instrucciones para ocultar la hoja de validaciones
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
