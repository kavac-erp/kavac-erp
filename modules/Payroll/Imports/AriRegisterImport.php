<?php

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Payroll\Models\PayrollStaff;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Payroll\Models\PayrollAriRegister;

class AriRegisterImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $payrollStaff =
            PayrollStaff::query()->where('id_number', $row['cedula'])->toBase()->first();

        $from_date =
            isset($row['desde']) ? (is_string($row['desde']) ? $row['desde'] : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['desde'])
            )) : null;

        $to_date =
            isset($row['hasta']) ? (is_string($row['hasta']) ? $row['hasta'] : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['hasta'])
            )) : null;

        if ($payrollStaff) {
            return new PayrollAriRegister([
                'payroll_staff_id' => $payrollStaff->id,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'percetage' => $row['porcentaje'] / 100
            ]);
        }
    }
}
