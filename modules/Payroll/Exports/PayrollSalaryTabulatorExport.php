<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;
use Modules\Payroll\Models\PayrollSalaryTabulator;

/**
 * @class PayrollSalaryTabulatorExport
 * @brief Clase que gestiona los objetos exportados del modelo de tabuladores salariales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryTabulatorExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize
{
    /**
     * Identificador del tabulador salarial
     *
     * @var integer $payrollSalaryTabulatorId
     */
    protected $payrollSalaryTabulatorId;

    /**
     * Método constructor de la clase
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * Establece el identificador del tabulador salarial
     *
     * @param integer $salaryTabulatorId Identificador único del tabuldor salarial
     */
    public function setSalaryTabulatorId(int $salaryTabulatorId)
    {
        $this->payrollSalaryTabulatorId = $salaryTabulatorId;
    }

    /**
     * Genera el listado de tabuladores salariales
     *
     * @return \Illuminate\Support\Collection|void
     */
    public function collection()
    {
        $payrollSalaryTabulator = PayrollSalaryTabulator::where('id', $this->payrollSalaryTabulatorId)->first();
        $fields  = [];
        $records = [];
        if ($payrollSalaryTabulator) {
            $payrollSalaryAdjustments = $payrollSalaryTabulator->payrollSalaryAdjustments();
            $payrollSalaryTabulatorScales = PayrollSalaryTabulatorScale::where([
                'payroll_salary_tabulator_id' => $this->payrollSalaryTabulatorId
            ])->with([
                'payrollSalaryTabulator',
                'payrollHorizontalScale',
                'payrollVerticalScale'
            ])->get();

            $salary_collection = $payrollSalaryAdjustments->get();
            if ($salary_collection->isNotEmpty()) {
                $payrollSalaryAdjustments = $payrollSalaryAdjustments->orderBy('created_at', 'desc')->get();
                $payrollHistorySalaryAdjustments =
                    $payrollSalaryAdjustments[0]
                    ->payrollHistorySalaryAdjustments()
                    ->orderBy('created_at', 'desc')->get();

                $salary_values = json_decode($payrollHistorySalaryAdjustments[0]->salary_values);
            }

            $count = 0;
            $type = $salary_collection->isNotEmpty() ? $payrollSalaryAdjustments[0]->increase_of_type : null;

            foreach ($payrollSalaryTabulatorScales as $payrollSalaryTabulatorScale) {
                if (($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) && ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0)) {
                    $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                    $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                    $fields[$horizontalScale->name . '-' . $verticalScale->name] =
                        isset($payrollSalaryAdjustments) && ($type == 'different') && isset($payrollHistorySalaryAdjustments) ?
                            $salary_values[$count]->value :
                            $payrollSalaryTabulatorScale->value;
                } elseif ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                    $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                    $fields[$horizontalScale->name] =
                        isset($payrollSalaryAdjustments) && ($type == 'different') && isset($payrollHistorySalaryAdjustments) ?
                            $salary_values[$count]->value :
                            $payrollSalaryTabulatorScale->value;
                } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                    $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                    $fields[$verticalScale->name] =
                    isset($payrollSalaryAdjustments) && ($type == 'different') && isset($payrollHistorySalaryAdjustments) ?
                        $salary_values[$count]->value :
                        $payrollSalaryTabulatorScale->value;
                }
                $count++;
            }

            if (($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) && ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0)) {
                $payrollHorizontalSalaryScale = $payrollSalaryTabulator->payrollHorizontalSalaryScale;
                $payrollVerticalSalaryScale = $payrollSalaryTabulator->payrollVerticalSalaryScale;

                foreach ($payrollVerticalSalaryScale->payrollScales as $payrollVerticalScale) {
                    array_push($records, $payrollVerticalScale->name);
                    foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollHorizontalScale) {
                        array_push(
                            $records,
                            $fields[$payrollHorizontalScale->name . '-' . $payrollVerticalScale->name]
                        );
                    }
                }
                $records = array_chunk($records, count($payrollHorizontalSalaryScale->payrollScales) + 1);
            } elseif ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                $payrollHorizontalSalaryScale = $payrollSalaryTabulator->payrollHorizontalSalaryScale;
                array_push($records, 'Incidencia');
                foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollHorizontalScale) {
                    array_push($records, $fields[$payrollHorizontalScale->name]);
                }
                $records = array_chunk($records, count($payrollHorizontalSalaryScale->payrollScales) + 1);
            } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                $payrollVerticalSalaryScale = $payrollSalaryTabulator->payrollVerticalSalaryScale;
                foreach ($payrollVerticalSalaryScale->payrollScales as $payrollVerticalScale) {
                    array_push($records, $payrollVerticalScale->name);
                    array_push($records, $fields[$payrollVerticalScale->name]);
                }
                $records = array_chunk($records, 2);
            }
            return new Collection($records);
        }
    }

    /**
     * Establece la cabecera del archivo exportado
     *
     * @return array Arreglo que contiene la estructura de cabecera del archivo exportado
     */
    public function headings(): array
    {
        $payrollSalaryTabulator = PayrollSalaryTabulator::where('id', $this->payrollSalaryTabulatorId)->first();
        $fields = [];
        if ($payrollSalaryTabulator) {
            if ($payrollSalaryTabulator->payroll_horizontal_salary_scale_id > 0) {
                array_push($fields, 'Nombre');
                $payrollHorizontalSalaryScale = $payrollSalaryTabulator->payrollHorizontalSalaryScale;
                foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollScale) {
                    array_push($fields, $payrollScale->name);
                }
            } elseif ($payrollSalaryTabulator->payroll_vertical_salary_scale_id > 0) {
                array_push($fields, 'Nombre');
                array_push($fields, 'Incidencia');
            }
            return $fields;
        } else {
            return [];
        }
    }
}
