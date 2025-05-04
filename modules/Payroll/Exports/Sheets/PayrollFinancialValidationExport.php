<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * @class PayrollFinancialValidationExport
 * @brief Clase para exportar la hoja de validaciones de datos financieros
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFinancialValidationExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    /**
     * Establece el título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'validation';
    }

    /**
     * Colección de datos a exportar

     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        if (!Module::has('Finance') && !Module::isEnabled('Finance')) {
            return collect([]);
        }
        $financeBanks = \Modules\Finance\Models\FinanceBank::query()->select('name')->get()->pluck('name')->toArray();
        $financeAccountTypes = \Modules\Finance\Models\FinanceAccountType::query()->select('name')->get()->pluck('name')->toArray();
        $maxCount = max(
            count($financeBanks),
            count($financeAccountTypes),
        );

        $financeBanks = array_pad($financeBanks, $maxCount, '');
        $financeAccountTypes = array_pad($financeAccountTypes, $maxCount, '');

        return collect(array_map(
            null,
            $financeBanks,
            $financeAccountTypes,
        ));
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Banco', 'Tipo de cuenta'];
    }

    /**
     * Registro de eventos de la hoja
     *
     * @return array
     */
    public function registerEvents(): array
    {
        /**
         * @todo Instrucciones para ocultar la hoja de validaciones
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
