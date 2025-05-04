<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * @class FailRegisterImportValuesExport
 * @brief Clase que exporta el listado de errores de la importación
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FailRegisterImportValuesExport implements WithMultipleSheets
{
    use Exportable;

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
     * Datos de las hojas a exportar
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        if (array_key_exists('Datos Personales', $this->errors)) {
            $sheets['Datos Personales'] = new StaffExport($this->errors['Datos Personales']);
        }
        if (array_key_exists('Datos Profesionales', $this->errors)) {
            $sheets['Datos Profesionales'] = new ProfessionalStaffExport($this->errors['Datos Profesionales']);
        }
        if (array_key_exists('Datos Socioeconomicos', $this->errors)) {
            $sheets['Datos Socioeconomicos'] = new SocioStaffExport($this->errors['Datos Socioeconomicos']);
        }
        if (array_key_exists('Datos Laborales', $this->errors)) {
            $sheets['Datos Laborales'] = new EmploymentStaffExport($this->errors['Datos Laborales']);
        }
        if (array_key_exists('Datos Financieros', $this->errors)) {
            $sheets['Datos Financieros'] = new FinancialStaffExport($this->errors['Datos Financieros']);
        }
        return $sheets;
    }
}
