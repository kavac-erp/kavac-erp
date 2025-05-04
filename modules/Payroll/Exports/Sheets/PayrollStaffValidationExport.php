<?php

namespace Modules\Payroll\Exports\Sheets;

use App\Models\Parish;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollBloodType;
use Modules\Payroll\Models\PayrollDisability;
use Modules\Payroll\Models\PayrollNationality;
use Modules\Payroll\Models\PayrollGender;
use Modules\Payroll\Models\PayrollLicenseDegree;

/**
 * @class PayrollStaffValidationExport
 * @brief Genera el archivo de validación de los trabajadores
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffValidationExport implements
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
        $nationalities = PayrollNationality::query()->select('name')->get()->pluck('name')->toArray();
        $genders = PayrollGender::query()->select('name')->get()->pluck('name')->toArray();
        $bloodTypes = PayrollBloodType::query()->select('name')->get()->pluck('name')->toArray();
        $disabilitys = PayrollDisability::query()->select('name')->get()->pluck('name')->toArray();
        $licenses = PayrollLicenseDegree::query()->select('name')->get()->pluck('name')->toArray();
        $parishes = Parish::query()->select('name')->get()->pluck('name')->toArray();
        $decisions = ['Si', 'No'];
        $maxCount = max(
            count($nationalities),
            count($genders),
            count($bloodTypes),
            count($decisions),
            count($disabilitys),
            count($licenses),
            count($parishes),
        );

        $nationalities = array_pad($nationalities, $maxCount, '');
        $genders = array_pad($genders, $maxCount, '');
        $bloodTypes = array_pad($bloodTypes, $maxCount, '');
        $decisions = array_pad($decisions, $maxCount, '');
        $disabilitys = array_pad($disabilitys, $maxCount, '');
        $licenses = array_pad($licenses, $maxCount, '');
        $parishes = array_pad($parishes, $maxCount, '');

        return collect(array_map(
            null,
            $nationalities,
            $genders,
            $disabilitys,
            $bloodTypes,
            $licenses,
            $parishes,
            $decisions
        ));
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Nacionalidad', 'Genero', 'Discapacidad', 'Tipo de sangre', 'Grado de licencia', 'Parroquia', 'Desicion'];
    }

    /**
     * Registro de eventos para la hoja
     *
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
