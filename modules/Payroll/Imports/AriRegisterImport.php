<?php

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Payroll\Models\PayrollStaff;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Payroll\Models\PayrollAriRegister;

/**
 * @class AriRegisterImport
 * @brief Importa un archivo de registros ARI
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AriRegisterImport implements
    ToModel,
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
     * Modelo para importar datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
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

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo con los datos
     * @param integer $index Indice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        $payrollStaff =
            PayrollStaff::query()->where('id_number', $data['cedula'])->toBase()->first();

        $from_date =
            isset($data['desde']) ? (is_string($data['desde']) ? $data['desde'] : Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data['desde'])
            )) : null;

        if (!empty($from_date) && !empty($payrollStaff)) {
            $payrollAriRegistersFromDate = PayrollAriRegister::query()
                ->where('payroll_staff_id', $payrollStaff->id)
                ->where('from_date', $from_date)
                ->where('deleted_at', null)
                ->get()
                ->toBase();
        }

        if (count($payrollAriRegistersFromDate) > 0) {
            $data["unique_from_date"] = true;
        }

        return $data;
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "unique_from_date" => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'La fecha inicial ya ha sido registrado por este empleado.'
                    );
                }
            },
            "cedula" => ['required'],
            "porcentaje" => ['required'],
            "desde" => ['required'],
        ];
    }
}
