<?php

namespace Modules\Asset\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * @class SecondSheetExport
 * @brief Gestiona la exportación de datos de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SecondSheetExport implements FromCollection, WithEvents, WithHeadings, WithTitle
{
    /**
     * Metodo que define el nombre de la hoja
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
        return collect([
            ['John Doe', 'john@example.com', '123456789'],
            ['Jane Smith', 'jane@example.com', '987654321'],
        ]);
    }

    /**
     * Metodo para definir los encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Nombre', 'Email', 'Teléfono'];
    }

    /**
     * Metodo para registrar los eventos de la hoja
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
                /**$worksheet->setSheetState('hidden');*/
            },
        ];
    }
}
