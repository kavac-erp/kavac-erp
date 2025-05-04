<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Payroll\Models\PayrollStaff;

class PayrollStaffAccountValidationExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    public function title(): string
    {
        return 'validation';
    }

    public function collection(): Collection
    {
        $staff = PayrollStaff::query()
            ->select('first_name', 'last_name', 'id_number', 'passport')
            ->get()
            ->toBase()
            ->map(function ($query) {
                return ($query->id_number ?? $query->passport) . ' - ' . $query->first_name . ' ' . $query->last_name;
            })
            ->toArray();

        $accountingAccount = AccountingAccount::query()
            ->get()
            ->toBase()
            ->map(function ($query) {
                return $query->code . ' - ' . $query->denomination;
            })
            ->toArray();

        $maxCount = max(
            count($staff),
            count($accountingAccount)
        );

        $staff = array_pad($staff, $maxCount, '');
        $accountingAccount = array_pad($accountingAccount, $maxCount, '');

        return collect(array_map(
            null,
            $staff,
            $accountingAccount,
        ));
    }

    public function headings(): array
    {
        return ['trabajadores', 'cuenta contable'];
    }

    public function registerEvents(): array
    {
        /** @todo Instrucciones para ocultar la hoja de validaciones
         * Descomentar cuando este verificada la hoja
         */
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                $worksheet->setSheetState('hidden');
            },
        ];
    }
}
