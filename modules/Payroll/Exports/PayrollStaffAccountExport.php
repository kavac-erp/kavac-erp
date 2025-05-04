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
use Modules\Payroll\Models\PayrollStaffAccount;

/**
 * @class PayrollStaffAccountExport
 * @brief Clase que exporta el listado de datos contables del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffAccountExport extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents
{
    use RegistersEventListeners;

    /**
     * Encabezados de las columnas de la hoja a exportar
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'trabajadores',
            'cuenta_contable',
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param array $data Fila con datos
     *
     * @return array
     */
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

    /**
     * Título de la hoja a exportar
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Contables';
    }

    /**
     * Establece los eventos de la hoja
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                $sheet = $event->sheet->getDelegate();
                /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                $validationRangeA = 'validation!$A$2:$A$5000';
                $validationRangeB = 'validation!$B$2:$B$5000';

                $this->setFunctionList($sheet, 'A', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'B', 2, null, 'validateB', $validationRangeB);
            },
        ];
    }
}
