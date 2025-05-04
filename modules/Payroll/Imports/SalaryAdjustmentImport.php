<?php

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Payroll\Models\PayrollHistorySalaryAdjustment;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Modules\Payroll\Models\PayrollSalaryAdjustment;

/**
 * @class SalaryAdjustmentImport
 * @brief Importa un archivo de ajuste salarial del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SalaryAdjustmentImport implements
    OnEachRow,
    WithValidation,
    WithHeadingRow,
    SkipsEmptyRows,
    SkipsOnError,
    SkipsOnFailure
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    /**
     * Metodo que se encarga de importar el archivo
     *
     * @param array $row Fila de datos a importar
     *
     * @return void
     */
    public function onRow(Row $row)
    {
        $payrollSalaryTabulator =
            PayrollSalaryTabulator::query()->where('name', $row['tabulador_salarial'])->toBase()->first();

        $created_date =
            isset($row['fecha_de_generacion']) ?
                (is_string($row['fecha_de_generacion']) ?
                    $row['fecha_de_generacion'] :
                    Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_generacion'])
                    )) : null;

        $increase_of_date =
            isset($row['fecha_de_aumento']) ?
                (is_string($row['fecha_de_aumento']) ?
                    $row['fecha_de_aumento'] :
                    Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_de_aumento'])
                    )) : null;

        $end_increase_date =
            isset($row['fecha_fin_de_aumento']) ?
                (is_string($row['fecha_fin_de_aumento']) ?
                    $row['fecha_fin_de_aumento'] :
                    Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha_fin_de_aumento'])
                    )) : null;

        $type_array = ['different', 'absolute_value', 'percentage'];

        if ($row['tipo_de_aumento'] == 'Diferente') {
            $type_name = $type_array[0];
        } elseif ($row['tipo_de_aumento'] == 'Porcentual') {
            $type_name = $type_array[2];
        } else {
            $type_name = $type_array[1];
        }

        if ($payrollSalaryTabulator) {
                $payrollSalaryAdjustment = PayrollSalaryAdjustment::firstOrCreate([
                    'increase_of_type'                   => $type_name,
                    'value'                              => $row['valor'],
                    'payroll_salary_tabulator_id'        => $payrollSalaryTabulator->id,
                ]);

                $payrollSalaryAdjustment->created_at = $created_date;
                $payrollSalaryAdjustment->save();

                PayrollHistorySalaryAdjustment::firstOrCreate([
                    'increase_of_date'                   => $increase_of_date,
                    'end_increase_date'                  => $end_increase_date,
                    'payroll_salary_adjustment_id'       => $payrollSalaryAdjustment->id
                ]);
        }
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "fecha_de_generacion" => ['date', 'required'],
            "fecha_de_aumento" => ['date', 'required'],
            "fecha_fin_de_aumento" => ['date', 'nullable'],
            "valor" => ['required'],
            "tabulador_salarial" => ['required'],
        ];
    }
}
