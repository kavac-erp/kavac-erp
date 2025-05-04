<?php

namespace Modules\Payroll\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * @class SalaryTabulatorImport
 * @brief Importa un archivo de tabulador salarial del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SalaryTabulatorImport extends \App\Imports\DataImport implements ToCollection
{
    /**
     * Colección de datos a importar
     *
     * @param array $row
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(Collection $rows)
    {
        /* Datos de los productos a importar */
        return $rows;
    }
}
