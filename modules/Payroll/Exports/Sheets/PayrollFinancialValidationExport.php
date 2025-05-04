<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Finance\Models\FinanceAccountType;
use Modules\Finance\Models\FinanceBank;

class PayrollFinancialValidationExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    public function title(): string
    {
        return 'validation';
    }

    public function collection(): Collection
    {
        $financeBanks = FinanceBank::query()->select('name')->get()->pluck('name')->toArray();
        $financeAccountTypes = FinanceAccountType::query()->select('name')->get()->pluck('name')->toArray();
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

    public function headings(): array
    {
        return ['Banco', 'Tipo de cuenta'];
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
