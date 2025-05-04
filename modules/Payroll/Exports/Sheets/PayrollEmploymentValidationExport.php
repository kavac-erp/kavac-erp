<?php

namespace Modules\Payroll\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\Department;
use Modules\Payroll\Models\PayrollContractType;
use Modules\Payroll\Models\PayrollInactivityType;
use Modules\Payroll\Models\PayrollPosition;
use Modules\Payroll\Models\PayrollCoordination;
use Modules\Payroll\Models\PayrollPositionType;
use Modules\Payroll\Models\PayrollSectorType;
use Modules\Payroll\Models\PayrollStaffType;

/**
 * @class PayrollEmploymentValidationExport
 * @brief Clase para exportar la hoja de validaciones de la nómina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollEmploymentValidationExport implements
    FromCollection,
    WithEvents,
    WithHeadings,
    WithTitle
{
    /**
     * Establece el título de la hoja
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
        $inactivityTypes = PayrollInactivityType::query()->select('name')->get()->pluck('name')->toArray();
        $positionTypes = PayrollPositionType::query()->select('name')->get()->pluck('name')->toArray();
        $positions = PayrollPosition::query()->where('responsible', false)->select('name')->get()->pluck('name')->toArray();
        $coordinations = PayrollCoordination::query()->select('name')->get()->pluck('name')->toArray();
        $staffTypes = PayrollStaffType::query()->select('name')->get()->pluck('name')->toArray();
        $contractTypes = PayrollContractType::query()->select('name')->get()->pluck('name')->toArray();
        $departments = Department::query()->select('name')->get()->pluck('name')->toArray();
        $sectorTypes = PayrollSectorType::query()->select('name')->get()->pluck('name')->toArray();
        $decisions = ['Si', 'No'];
        $maxCount = max(
            count($inactivityTypes),
            count($positionTypes),
            count($positions),
            count($coordinations),
            count($staffTypes),
            count($contractTypes),
            count($departments),
            count($sectorTypes),
            count($decisions),
        );

        $inactivityTypes = array_pad($inactivityTypes, $maxCount, '');
        $positionTypes = array_pad($positionTypes, $maxCount, '');
        $positions = array_pad($positions, $maxCount, '');
        $coordinations = array_pad($coordinations, $maxCount, '');
        $staffTypes = array_pad($staffTypes, $maxCount, '');
        $contractTypes = array_pad($contractTypes, $maxCount, '');
        $departments = array_pad($departments, $maxCount, '');
        $sectorTypes = array_pad($sectorTypes, $maxCount, '');
        $decisions = array_pad($decisions, $maxCount, '');

        return collect(array_map(
            null,
            $inactivityTypes,
            $positionTypes,
            $positions,
            $staffTypes,
            $contractTypes,
            $departments,
            $sectorTypes,
            $decisions,
            $coordinations,
        ));
    }

    /**
     * Encabezado de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tipo de inactividad',
            'Tipo de cargo',
            'Cargo',
            'Tipo de personal',
            'Tipo de contrato',
            'Departamento',
            'Tipo de sector',
            'Desicion',
            'Coordinacion',
        ];
    }

    /**
     * Registro de eventos al exportar la hoja
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
