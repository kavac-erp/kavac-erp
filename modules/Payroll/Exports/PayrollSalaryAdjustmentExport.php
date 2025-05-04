<?php

namespace Modules\Payroll\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

/**
 * @class PayrollSalaryAdjustmentExport
 * @brief Clase que exporta el listado de ajustes salariales
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAdjustmentExport implements
    FromCollection,
    ShouldQueue,
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithStrictNullComparison
{
    use Exportable;

    /**
     * Genera el listado de ajustes salariales
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function collection()
    {
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::with([
            'payrollSalaryTabulator',
            'payrollHistorySalaryAdjustments' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            }
        ])->get();

        return $payrollSalaryAdjustment;
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Fecha de generación',
            'Fecha de aumento',
            'Fecha fin de aumento',
            'Tipo de aumento',
            'Valor',
            'Tabulador salarial'
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param array|object $row datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        $type_array = ['Diferente', 'Valor Absoluto', 'Porcentual'];

        if ($row->increase_of_type == 'different') {
            $type_name = $type_array[0];
        } elseif ($row->increase_of_type == 'percentage') {
            $type_name = $type_array[2];
        } else {
            $type_name = $type_array[1];
        }

        $value = $row->value ? $row->value : 0;
        return [
            Carbon::parse($row->created_at)->format("Y-m-d"),
            $row->payrollHistorySalaryAdjustments[0]->increase_of_date,
            $row->payrollHistorySalaryAdjustments[0]->end_increase_date,
            $type_name,
            $value,
            $row->payrollSalaryTabulator->name,
        ];
    }
}
