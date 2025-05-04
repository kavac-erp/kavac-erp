<?php

namespace Modules\Asset\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class SecondSheetExport implements FromCollection, WithEvents, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'validation';
    }

    public function collection(): Collection
    {
        return collect([
            ['John Doe', 'john@example.com', '123456789'],
            ['Jane Smith', 'jane@example.com', '987654321'],
        ]);
    }

    public function headings(): array
    {
        return ['Nombre', 'Email', 'Teléfono'];
    }

    public function registerEvents(): array
    {
        /** @todo Instrucciones para ocultar la hoja de validaciones
         * Descomentar cuando este verificada la hoja
         */
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                /**$worksheet->setSheetState('hidden');*/
            },
        ];
    }
}
