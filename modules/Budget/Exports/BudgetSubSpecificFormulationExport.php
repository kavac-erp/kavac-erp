<?php

namespace Modules\Budget\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class BudgetSubSpecificFormulationExport
 * @brief Exporta datos de la formulación de presupuesto
 *
 * Gestiona la exportación de datos de la formulación de presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSubSpecificFormulationExport implements WithHeadings, ShouldAutoSize, WithMapping, FromCollection, WithEvents
{
    use Exportable;

    /**
     * Identificador de la formulación
     *
     * @var integer $budgetFormulationId
     */
    protected $budgetFormulationId;

    /**
     * Clase del modelo del cual exportar datos
     *
     * @var string|object|BudgetSubSpecificFormulation $model
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Establece el identificador del registro de nómina
     *
     * @param integer $budgetFormulationId Identificador único del tabuldor salarial
     *
     * @return void
     */
    public function setbudgetFormulationId(int $budgetFormulationId)
    {
        $this->budgetFormulationId = $budgetFormulationId;
        $this->model = BudgetSubSpecificFormulation::query()->where('id', $budgetFormulationId)->get();
    }

    /**
     * Obtiene la colección de registros a exportar
     *
     * @return    \Illuminate\Support\Collection
     */
    public function collection()
    {
        $formulation = BudgetSubSpecificFormulation::query()->with([
            'currency',
            'institution'
        ])->where('id', $this->budgetFormulationId)
        ->get();

        return $formulation;
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        $headings = [[
            'Fecha de generación'
            ]
        ];
        $headings[] = [
            'Presupuesto asignado'
        ];
        $headings[] = [
            'Institución'
        ];
        $headings[] = [
            'Moneda'
        ];
        $headings[] = [
            'Presupuesto'
        ];
        $headings[] = [
            'Acción centralizada'
        ];
        $headings[] = [
            'Acción específica'
        ];
        $headings[] = [
            'Fuente de financiamiento'
        ];
        $headings[] = [
            'Tipo de financiamiento'
        ];
        $headings[] = [
            'Monto del financiamiento'
        ];
        $headings[] = [
            'Total formulado'
        ];
        $headings[] = [' ', ' ', ' '];
        $headings[] = ['Código', 'Denominación', 'Total año'];


        return $headings;
    }

    /**
     * Mapea los registros a las columnas del archivo a exportar
     *
     * @param mixed $row Datos del registro a exportar
     *
     * @return array
     */
    public function map($row): array
    {
        $array = [];

        foreach ($row->accountOpens as $accountOpen) {
            $code = $accountOpen?->budgetAccount?->code;
            $denomination = $accountOpen?->budgetAccount?->denomination;
            $total_year = number_format(
                $accountOpen->total_year_amount,
                $row->currency->decimal_places,
                ",",
                "."
            );

            $array[] = [$code, $denomination, $total_year];
        }

        $total_formulated = number_format(
            $row->total_formulated,
            $row->currency->decimal_places,
            ",",
            "."
        );
        $array[] = ['Total Formulado', '', $total_formulated];

        array_multisort(
            array_column($array, 0),
            SORT_ASC,
            array_column($array, 1),
            SORT_ASC,
            array_column($array, 2),
            SORT_ASC,
            $array
        );

        return $array;
    }

    /**
     * Establece los eventos de la exportación de datos
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $records = $this->model;

        $data = [
            AfterSheet::class => function (AfterSheet $event) use ($records) {
                $sheet = $event->sheet;

                $sheet->setCellValue('B1', $records[0]->date ? date("d/m/Y", strtotime($records[0]->date)) : 'Sin fecha asignada');
                $sheet->setCellValue('B2', ($records[0]?->assigned || $records[0]?->assigned === '1') ? 'Sí' : 'No');
                $sheet->setCellValue('B3', $records[0]?->specificAction ? $records[0]?->specificAction?->institution : 'N/A');
                $sheet->setCellValue('B4', $records[0]?->currency ? $records[0]?->currency?->description : 'N/A');
                $sheet->setCellValue('B5', $records[0]?->year ? $records[0]?->year : 'N/A');
                $sheet->setCellValue('B6', $records[0]?->specificAction->specificable->code . ' - ' .
                    $records[0]?->specificAction?->specificable?->name ??
                    'N/A');
                $sheet->setCellValue('B7', $records[0]?->specificAction?->code . ' - ' .
                    $records[0]?->specificAction?->name ??
                    'N/A');
                $sheet->setCellValue('B8', $records[0]?->budgetFinancementType ?
                    $records[0]?->budgetFinancementType?->name :
                    'N/A');
                $sheet->setCellValue('B9', $records[0]?->budgetFinancementSource ?
                    $records[0]?->budgetFinancementSource?->name :
                    'N/A');
                $sheet->setCellValue('B10', $records[0]->currency->symbol . ' ' .
                    number_format(
                        $records[0]->financement_amount,
                        $records[0]->currency->decimal_places,
                        ',',
                        '.'
                    ));
                $sheet->setCellValue('B11', $records[0]->currency->symbol . ' ' .
                    number_format(
                        $records[0]->total_formulated,
                        $records[0]->currency->decimal_places,
                        ',',
                        '.'
                    ));

                $sheet->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('A4')->getFont()->setBold(true);
                $sheet->getStyle('A5')->getFont()->setBold(true);
                $sheet->getStyle('A6')->getFont()->setBold(true);
                $sheet->getStyle('A7')->getFont()->setBold(true);
                $sheet->getStyle('A8')->getFont()->setBold(true);
                $sheet->getStyle('A9')->getFont()->setBold(true);
                $sheet->getStyle('A10')->getFont()->setBold(true);
                $sheet->getStyle('A11')->getFont()->setBold(true);
                $sheet->getStyle('A13')->getFont()->setBold(true);
                $sheet->getStyle('B13')->getFont()->setBold(true);
                $sheet->getStyle('C13')->getFont()->setBold(true);

                $highestRow = $sheet->getHighestRow();

                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    if ($cellValue === 'Total Formulado') {
                        $sheet->setCellValueByColumnAndRow(
                            1,
                            $row,
                            $cellValue . ' ' . $records[0]->currency->symbol
                        );
                        $sheet->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
                        $sheet->getStyleByColumnAndRow(3, $row)->getFont()->setBold(true);
                    }
                }
            },
        ];

        return $data;
    }
}
