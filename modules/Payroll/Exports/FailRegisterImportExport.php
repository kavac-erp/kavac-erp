<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Log;

/**
 * @class FailRegisterImportExport
 * @brief Clase que exporta el listado de errores de la importación
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FailRegisterImportExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /**
     * Lista de errores
     *
     * @var array $errors
     */
    protected $errors;

    /**
     * Método constructor de la clase.
     *
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Encabezados de las columnas de la hoja a exportar
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Fila',
            'Atributo',
            'Error',
            'Hoja de Calculo',
        ];
    }

    /**
     * Retorna los errores de la hoja
     *
     * @return array
     */
    public function array(): array
    {
        Log::info(json_encode($this->errors));

        return $this->errors;
    }
}
