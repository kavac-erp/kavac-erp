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

class PayrollStaffValidationExport implements
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

    public function headings(): array
    {
        return ['Nacionalidad', 'Genero', 'Discapacidad', 'Tipo de sangre', 'Grado de licencia', 'Parroquia', 'Desicion'];
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
