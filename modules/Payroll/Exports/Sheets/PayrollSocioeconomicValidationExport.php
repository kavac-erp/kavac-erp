<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\MaritalStatus;
use Modules\Payroll\Models\PayrollDisability;
use Modules\Payroll\Models\PayrollSchoolingLevel;
use Modules\Payroll\Models\PayrollScholarshipType;
use Modules\Payroll\Models\PayrollRelationship;
use App\Models\Gender;

class PayrollSocioeconomicValidationExport implements
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
        $PayrollScholarshipType = PayrollScholarshipType::query()->select('name')->get()->pluck('name')->toArray();
        $PayrollRelationship = PayrollRelationship::query()->select('name')->get()->pluck('name')->toArray();
        $maritalStatus = MaritalStatus::query()->select('name')->get()->pluck('name')->toArray();
        $Gender = Gender::query()->select('name')->get()->pluck('name')->toArray();
        $schoolingLevels = PayrollSchoolingLevel::query()->select('name')->get()->pluck('name')->toArray();
        $disabilities = PayrollDisability::query()->select('name')->get()->pluck('name')->toArray();
        $decisions = ['Si', 'No'];
        $maxCount = max(
            count($maritalStatus),
            count($schoolingLevels),
            count($disabilities),
            count($decisions),
            count($PayrollScholarshipType),
            count($PayrollRelationship),
            count($Gender),
        );

        $maritalStatus = array_pad($maritalStatus, $maxCount, '');
        $schoolingLevels = array_pad($schoolingLevels, $maxCount, '');
        $disabilities = array_pad($disabilities, $maxCount, '');
        $decisions = array_pad($decisions, $maxCount, '');
        $PayrollScholarshipType = array_pad($PayrollScholarshipType, $maxCount, '');
        $PayrollRelationship = array_pad($PayrollRelationship, $maxCount, '');
        $Gender = array_pad($Gender, $maxCount, '');

        return collect(array_map(
            null,
            $maritalStatus,
            $schoolingLevels,
            $disabilities,
            $decisions,
            $PayrollScholarshipType,
            $PayrollRelationship,
            $Gender,
        ));
    }

    public function headings(): array
    {
        return ['Estado civil', 'Nivel de escolaridad', 'Discapacidad', 'Desicion','tipo de beca','Parentesco','Genero' ];
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
