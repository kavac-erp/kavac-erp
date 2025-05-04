<?php

namespace Modules\Accounting\Exports;

use Modules\Accounting\Models\AccountingAccount;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountingAccountExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AccountingAccount::get();
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @method    headings
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        return [
            'CÓDIGO',
            'DENOMINACION',
            'TIPO DE CUENTA',
            'ACTIVA',
            'ORIGINAL',
        ];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @method    map
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     *
     * @param     object    $record    Objeto con las propiedades del modelo a exportar
     *
     * @return    array     Arreglo con los campos estrictamente a ser exportados
     */
    public function map($record): array
    {
        return [
            $record->getCodeAttribute(),
            $record->denomination,
            $record->resource ? 'INGRESO' : ($record->egress ? 'EGRESO' : ''),
            $record->active ? 'SI' : 'NO',
            $record->original ? 'SI' : 'NO',
        ];
    }
}
