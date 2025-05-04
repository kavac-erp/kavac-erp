<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * @class PayrollTimeSheetExport
 * @brief Clase que exporta el listado de registros de la hoja de tiempo
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithCustomStartCell
{
    /**
     * Encabezados de la hoja
     *
     * @var array $headings
     */
    protected $headings;

    /**
     * Colección de datos a exportar
     *
     * @var array $collection
     */
    protected $collection;

    /**
     * Método constructor de la clase
     *
     * @param array $collection Colección de datos
     *
     * @return void
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Genera el listado de registros de la hoja de tiempo
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];

        foreach ($this->collection['params']['data'] as $row) {
            if (!array_key_exists($row['staff_id'], $data)) {
                $data[$row['staff_id']] = [
                    'N°' => $row['N°'],
                    'Cedula' => $row['id_number'],
                    'Ficha' => $row['Ficha'],
                    'Nombre' => $row['Nombre'],
                ];
            }

            foreach ($this->collection['params']['inputValues'] as $key => $value) {
                if (
                    !str_contains($key, 'Total') &&
                    !str_contains($key, 'total') &&
                    !str_contains($key, 'Conceptos') &&
                    !str_contains($key, 'Observación')
                ) {
                    $lastIndex = strrpos($key, '-');

                    if ($lastIndex !== false) {
                        $beforeHyphen = trim(substr($key, 0, $lastIndex));
                        $afterHyphen = trim(substr($key, $lastIndex + 1));

                        if ($row['staff_id'] == $afterHyphen) {
                            $data[$row['staff_id']][$beforeHyphen] = $value;
                        }
                    }
                }
            }
        }

        return collect($data)->values();
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
        $headings = [];

        foreach ($this->collection['params']['columns'] as $column) {
            if (
                !str_contains($column['name'], 'Total') &&
                !str_contains($column['name'], 'total') &&
                !str_contains($column['name'], 'Observación') &&
                !str_contains($column['name'], 'Conceptos')
            ) {
                $headings[] = $column['name'];
            }
        }

        $this->headings = $headings;

        return $headings;
    }

    /**
     * Establece los datos que van a ser exportados
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     object    $params    Objeto con las propiedades del modelo a exportar
     *
     * @return    array     Arreglo con los campos estrictamente a ser exportados
     */
    public function map($params): array
    {
        $realParams = [];

        foreach ($this->headings as $key) {
            if (array_key_exists($key, (array)$params)) {
                $realParams[] = $params[$key];
            } else {
                $realParams[] = null;
            }
        }

        return [$realParams];
    }
}
