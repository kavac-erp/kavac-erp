<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollAriRegister;

class PayrollAriRegisterExport implements FromCollection, ShouldQueue, WithHeadings, ShouldAutoSize, WithMapping
{
    use Exportable;

    public function collection()
    {
        $columns = ['payroll_staff_id', 'percetage', 'from_date', 'to_date'];

        return PayrollAriRegister::with('payrollStaff')->get($columns);
    }

    public function headings(): array
    {
        return [
            'Cédula',
            'Porcentaje',
            'Desde',
            'Hasta'
        ];
    }

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
