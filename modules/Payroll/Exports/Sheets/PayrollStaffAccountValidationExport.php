<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollStaff;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * @class PayrollStaffAccountValidationExport
 * @brief Genera el archivo de validación de las cuentas Contables de los trabajadores
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffAccountValidationExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    /**
     * Establece el tiúlo de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'validation';
    }

    /**
     * Colección de datos a exportar

     * @return \Illuminate\Support\Collection
     */
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


        $accountingAccount = (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? \Modules\Accounting\Models\AccountingAccount::query()->get()->toBase()->map(function ($query) {
            return $query->code . ' - ' . $query->denomination;
        })->toArray() : [];

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

    /**
     * Encabezados de la hoja

     * @return array
     */
    public function headings(): array
    {
        return ['trabajadores', 'cuenta contable'];
    }

    /**
     * Registro de eventos para la hoja

     * @return array
     */
    public function registerEvents(): array
    {
        /**
         * @todo Instrucciones para ocultar la hoja de validaciones
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
