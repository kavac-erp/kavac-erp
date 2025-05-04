<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollStaffAccount;

class PayrollStaffAccountExport extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents
{
    use RegistersEventListeners;

    public function headings(): array
    {
        return [
            'trabajadores',
            'cuenta_contable',
        ];
    }
    public function map($data): array
    {
        $staff = PayrollStaffAccount::query()
            ->with(['payrollStaff', 'accountingAccount'])
            ->where('payroll_staff_id', $data['payroll_staff_id'])
            ->where('accounting_account_id', $data['accounting_account_id'])
            ->first();

        $map = [
            $data['trabajadores'] = $staff->payrollStaff->id_number . ' - ' .
                $staff->payrollStaff->first_name . ' ' .
                $staff->payrollStaff->last_name,
            $data['cuenta_contable'] = $staff->accountingAccount->code . ' - ' .
                $staff->accountingAccount->denomination,
        ];

        return $map;
    }

    public function title(): string
    {
        return 'Datos Contables';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** Se crea una instancia Worksheet para acceder a las dos sheet. */
                $sheet = $event->sheet->getDelegate();
                /** Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                $validationRangeA = 'validation!$A$2:$A$5000';
                $validationRangeB = 'validation!$B$2:$B$5000';

                $this->setFunctionList($sheet, 'A', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'B', 2, null, 'validateB', $validationRangeB);
            },
        ];
    }
}
