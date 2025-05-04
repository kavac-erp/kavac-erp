<?php

namespace Modules\Payroll\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

/**
 * @class CompleteStaffImport
 * @brief Importa un archivo de expediente digital del personal completo
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CompleteStaffImport implements WithMultipleSheets, SkipsUnknownSheets
{
    use Importable;
    use SkipsFailures;

    /**
     * Array de hojas a importar
     *
     * @var array
     */
    private $sheets;

    /**
     * Método para obtener las hojas a importar
     *
     * @return array
     */
    public function sheets(): array
    {
        $this->sheets = [
            'Datos Personales' => new StaffImport(''),
            'Datos Profesionales' => new ProfessionalStaffImport(''),
            'Datos Socioeconomicos' => new SocioStaffImport(''),
            'Datos Laborales' => new EmploymentStaffImport(''),
            'Datos Financieros' => new FinancialStaffImport(''),
            'Datos Contables' => new StaffAccountImport(''),
        ];

        return $this->sheets;
    }

    /**
     * Callback de error de hoja desconocida
     *
     * @param string $sheetName Nombre de la hoja
     *
     * @return void
     */
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }

    /**
     * Método para obtener las hojas fallidas
     *
     * @return array
     */
    public function failures()
    {
        $array = [];
        foreach ($this->sheets as $key => $sheet) {
            $array[$key] = $sheet->failures();
        }
        return $array;
    }
}
