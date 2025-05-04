<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * @class DataImports
 * @brief Gestión de la estructura de datos del sistema para la información a importar
 *
 * Permite exportar datos del sistema
 *
 * @author   Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DataImport implements ToCollection, WithHeadingRow
{
    use Importable;

    /**
     * Colección de registros a importar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Collection    $collection    Objeto con los datos a importar
     *
     * @return    Collection    Devuelve una colección de objetos
     */
    public function collection(Collection $collection)
    {
        return $collection;
    }
}
