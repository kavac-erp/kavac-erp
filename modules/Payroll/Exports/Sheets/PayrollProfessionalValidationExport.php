<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollInstructionDegree;
use Modules\Payroll\Models\PayrollLanguage;
use Modules\Payroll\Models\PayrollLanguageLevel;
use Modules\Payroll\Models\PayrollStudyType;
use Modules\Payroll\Models\Profession;

/**
 * @class PayrollProfessionalValidationExport
 * @brief Clase para exportar la hoja de validaciones de datos profesionales
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollProfessionalValidationExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    /**
     * Establece el título de la hoja
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
        $instructionDegrees = PayrollInstructionDegree::query()->select('name')->get()->pluck('name')->toArray();
        $studyTypes = PayrollStudyType::query()->select('name')->get()->pluck('name')->toArray();
        $professions = Profession::query()->select('name')->get()->pluck('name')->toArray();
        $languages = PayrollLanguage::query()->select('name')->get()->pluck('name')->toArray();
        $languageLevels = PayrollLanguageLevel::query()->select('name')->get()->pluck('name')->toArray();
        $decisions = ['Si', 'No'];
        $maxCount = max(
            count($instructionDegrees),
            count($studyTypes),
            count($professions),
            count($languages),
            count($languageLevels),
            count($decisions),
        );

        $instructionDegrees = array_pad($instructionDegrees, $maxCount, '');
        $studyTypes = array_pad($studyTypes, $maxCount, '');
        $professions = array_pad($professions, $maxCount, '');
        $languages = array_pad($languages, $maxCount, '');
        $languageLevels = array_pad($languageLevels, $maxCount, '');
        $decisions = array_pad($decisions, $maxCount, '');

        return collect(array_map(
            null,
            $instructionDegrees,
            $studyTypes,
            $professions,
            $languages,
            $languageLevels,
            $decisions,
        ));
    }

    /**
     * Encabezados de la hoja
     * @return array
     */
    public function headings(): array
    {
        return ['Grado de instruccion', 'Tipo de estudio', 'Profesion', 'Idioma', 'Nivel de idioma', 'Desicion'];
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
