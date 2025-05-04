<?php

namespace Modules\Purchase\Exports;

use Modules\Purchase\Models\PurchaseProduct;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * @class PurchaseProductExport
 * @brief Gestiona el proceso de exportación de datos de productos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseProductExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithCustomStartCell
{
    /**
     * Devuelve el listado de productos
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return PurchaseProduct::all();
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        return [
            'nombre',
            'codigo'
        ];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     object    $PurchaseProduct    Objeto con las propiedades del modelo a exportar
     *
     * @return    array     Arreglo con los campos estrictamente a ser exportados
     */
    public function map($PurchaseProduct): array
    {
        return [
            $PurchaseProduct->name,
            $PurchaseProduct->code
        ];
    }
}
