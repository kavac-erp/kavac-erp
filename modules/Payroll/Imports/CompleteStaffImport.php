<?php

namespace Modules\Payroll\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class CompleteStaffImport implements WithMultipleSheets, SkipsUnknownSheets
{
    use Importable;
    use SkipsFailures;

    private $sheets;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */


    public function sheets(): array
    {
        $this->sheets = [
            'Datos Personales' => new StaffImport(),
            'Datos Profesionales' => new ProfessionalStaffImport(),
            'Datos Socioeconomicos' => new SocioStaffImport(),
            'Datos Laborales' => new EmploymentStaffImport(),
            'Datos Financieros' => new FinancialStaffImport(),
            'Datos Contables' => new StaffAccountImport(),
        ];

        return $this->sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }


    public function failures()
    {
        $array = [];
        foreach ($this->sheets as $key => $sheet) {
            $array[$key] = $sheet->failures();
        }
        return $array;
    }
}
