<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollAriRegister;

/**
 * @class PayrollAriRegisterExport
 * @brief Clase que exporta el listado de registros de la planilla ARI
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollAriRegisterExport implements FromCollection, ShouldQueue, WithHeadings, ShouldAutoSize, WithMapping
{
    use Exportable;

    /**
     * Obtiene el listado de registros de la planilla ARI
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function collection()
    {
        $columns = ['payroll_staff_id', 'percetage', 'from_date', 'to_date'];

        return PayrollAriRegister::with('payrollStaff')->get($columns);
    }

    /**
     * Encabezados de las columnas de la hoja a exportar
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Cédula',
            'Porcentaje',
            'Desde',
            'Hasta'
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param object|array $row datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->payrollStaff->id_number,
            $row->percetage * 100,
            $row->from_date,
            $row->to_date,
        ];
    }
}
