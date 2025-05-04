<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollStaff;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

/**
 * @class FinancialStaffExport
 * @brief Clase que exporta el listado de datos financieros del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancialStaffExportFromButton extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents
{
    use RegistersEventListeners;

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'cedula_de_identidad',
            'banco',
            'tipo_de_cuenta',
            'numero_de_cuenta',
        ];
    }

    /**
     * Mapeo de los datos
     *
     * @param mixed $data Datos de la hoja
     *
     * @return array
     */
    public function map($data): array
    {
        $staff = PayrollStaff::find($data['payroll_staff_id']);
        $hasFinance = Module::has('Finance') && Module::isEnabled('Finance');
        $map = [
            $data['cedula'] = $staff->id_number,
            $hasFinance ? \Modules\Finance\Models\FinanceBank::find($data["finance_bank_id"] ?? null)?->name ?? '' : '',
            $hasFinance ? \Modules\Finance\Models\FinanceAccountType::find($data["finance_account_type_id"] ?? null)?->name ?? '' : '',
            $data["payroll_account_number"],
        ];
        return $map;
    }

    /**
     * Título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Financieros';
    }

    /**
     * Retorna los errores de la hoja
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

                $this->setFunctionList($sheet, 'B', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'C', 2, null, 'validateB', $validationRangeB);
            },
        ];
    }
}
