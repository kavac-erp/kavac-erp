<?php

namespace Modules\Asset\Exports\Sheets;

use App\Exports\DataExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class FirstSheetExport extends DataExport implements WithCustomStartCell, WithEvents, WithMapping, WithHeadings, WithTitle
{
    use RegistersEventListeners;

    /**
     * Constructor for the class.
     *
     * @param  string  $startCell    The total lines to be set as default.
     * @return void
     */
    public function __construct(
        private string $startCell = 'B4',
        protected int $totalLines = 10
    ) {
    }

    public function title(): string
    {
        return 'data';
    }

    public function startCell(): string
    {
        return $this->startCell;
    }

    public function headings(): array
    {
        /** Encabezados de la primera hoja */
        return [
            'Columna 1',
            'Columna 2',
        ];
    }

    public function collection(): Collection
    {
        /** Lógica para obtener los datos de la primera hoja */
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** Se crea una instancia Worksheet para acceder a las dos sheet. */
                $sheet = $event->sheet->getDelegate();
                /** Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                $validationRangeA = 'validation!$B$1:$B$20';
                $validationRangeB = 'validation!$C$1:$C$20';

                $this->setFunctionList($sheet, 'B', 4, 100, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'C', 4, 100, 'validateB', $validationRangeB);
            },
        ];
    }

    public function map($data): array
    {
        return [];
    }
}
