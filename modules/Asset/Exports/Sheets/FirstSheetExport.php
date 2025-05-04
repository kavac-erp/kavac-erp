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

/**
 * @class FirstSheetExport
 * @brief Exportar datos de la primera hoja del archivo
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FirstSheetExport extends DataExport implements WithCustomStartCell, WithEvents, WithMapping, WithHeadings, WithTitle
{
    use RegistersEventListeners;

    /**
     * Constructor de la clase
     *
     * @param  string  $startCell    Total de líneas por defecto
     *
     * @return void
     */
    public function __construct(
        private string $startCell = 'B4',
        protected int $totalLines = 10
    ) {
    }

    /**
     * Método que define el nombre de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'data';
    }

    /**
     * Método que define el punto de inicio de la hoja
     *
     * @return string
     */
    public function startCell(): string
    {
        return $this->startCell;
    }

    /**
     * Método que define los encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        /* Encabezados de la primera hoja */
        return [
            'Columna 1',
            'Columna 2',
        ];
    }

    /**
     * Método para obtener los datos de la hoja
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        /* Lógica para obtener los datos de la primera hoja */
        return collect([]);
    }

    /**
     * Método para registrar eventos de la hoja
     *
     * @return array
     */
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

    /**
     * Método para mapear los datos de la hoja
     *
     * @param mixed $data Datos de la hoja
     *
     * @return array
     */
    public function map($data): array
    {
        return [];
    }
}
