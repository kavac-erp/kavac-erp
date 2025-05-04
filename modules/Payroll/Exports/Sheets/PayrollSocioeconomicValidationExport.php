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

/**
 * @class PayrollSocioeconomicValidationExport
 * @brief Exporta la hoja de validaciones de los datos socioeconomicos del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSocioeconomicValidationExport implements
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
     *
     * @return \Illuminate\Support\Collection
     */
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

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Estado civil', 'Nivel de escolaridad', 'Discapacidad', 'Desicion','tipo de beca','Parentesco','Genero' ];
    }

    /**
     * Registro de eventos de la hoja
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
