<?php

namespace Modules\Accounting\Exports;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Accounting\Models\AccountingAccount;

/**
 * @class AccountingAccountExport
 * @brief Gestiona la exportación de datos de asientos contables
 *
 * Realiza el proceso de exportación de datos de asientos contables
 *
 * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingAccountExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping
{
    /**
     * Obtiene la colección de registros a exportar
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AccountingAccount::where('active', true)->with('parent')->get();
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
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
            'SUB-ESPECIFICA',
        ];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
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
            $record->parent_id ? $record->parent->code : null,
        ];
    }
}
