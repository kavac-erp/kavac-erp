<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Finance\Models\FinanceAccountType;
use Modules\Finance\Models\FinanceBank;
use Modules\Payroll\Models\PayrollStaff;

class FinancialStaffExportFromButton extends DataExport implements
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
            'cedula_de_identidad',
            'banco',
            'tipo_de_cuenta',
            'numero_de_cuenta',
        ];
    }
    public function map($data): array
    {
        $staff = PayrollStaff::find($data['payroll_staff_id']);
        $map = [
            $data['cedula'] = $staff->id_number,
            FinanceBank::find($data["finance_bank_id"] ?? null)?->name ?? '',
            FinanceAccountType::find($data["finance_account_type_id"] ?? null)?->name ?? '',
            $data["payroll_account_number"],
        ];
        return $map;
    }

    public function title(): string
    {
        return 'Datos Financieros';
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

                $this->setFunctionList($sheet, 'B', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'C', 2, null, 'validateB', $validationRangeB);
            },
        ];
    }
}
