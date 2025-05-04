<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * @class FinancialStaffExport
 * @brief Clase que exporta la información financiera de los trabajadores
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancialStaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
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
            'cedula_de_identidad',
            'banco',
            'tipo_de_cuenta',
            'numero_de_cuenta',
            'tipo_de_cuenta_value',
            'banco_value',
        ];
    }

    /**
     * Título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Financieros';
    }

    /**
     * Retorna los errores de la hoja
     *
     * @return array
     */
    public function array(): array
    {
        return $this->errors;
    }
}
