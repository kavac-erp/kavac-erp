<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * @class MultiSheetExport
 *
 * @brief Permite la exportación de datos con múltiples hojas
 *
 * Permite la exportación de datos con múltiples hojas
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class MultiSheetExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * Método constructor de la clase
     *
     * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  array $sheets Arreglo con datos de las hojas
     *
     * @return void
     */
    public function __construct(private array $sheets = [])
    {
        //
    }

    /**
     * Establece los datos de las hojas
     *
     * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  array $sheets Arreglo con datos de la hoja
     *
     * @return void
     */
    public function setSheets(array $sheets): void
    {
        $this->sheets = $sheets;
    }

    /**
     * Gestiona las hojas
     *
     * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return array
     */
    public function sheets(): array
    {
        return $this->sheets;
    }
}
