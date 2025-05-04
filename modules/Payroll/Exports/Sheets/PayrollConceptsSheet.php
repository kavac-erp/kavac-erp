<?php

namespace Modules\Payroll\Exports\Sheets;

use App\Models\User;
use App\Models\Profile;
use App\Models\Institution;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * @class PayrollConceptsSheet
 * @brief Hoja con los conceptos de la nómina
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com> | <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConceptsSheet implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithStyles,
    WithEvents,
    WithCustomStartCell
{
    /**
     * Lista de conceptos únicos
     *
     * @var array $uniqueConcepts
     */
    protected $uniqueConcepts;

    /**
     * Método constructor de la clase.
     *
     * @param array $uniqueConcepts
     *
     * @return void
     */
    public function __construct(array $uniqueConcepts)
    {
        $this->uniqueConcepts = $uniqueConcepts;
    }

    /**
     * Retorna los datos de la hoja
     *
     * @return array
     */
    public function array(): array
    {
        $data[] = ['', '', ''];
        foreach ($this->uniqueConcepts as $category => $concepts) {
            foreach ($concepts as $index => $concept) {
                $data[] = [
                    'Category' => $index === 0 ? $category : '',
                    'Concept' => $concept['name'],
                    'Formula' => $concept['formula'] ?? '',
                ];
            }
            $data[] = ['', '', ''];
        }

        return $data;
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'B5';
    }

    /**
     * Retorna las cabeceras de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return
        [
            ['Conceptos Utilizados en la nómina'],
            [''], // Fila en blanco
            ['Categoría', 'Concepto', 'Fórmula'],
        ];
    }

    /**
     * Establece el título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Conceptos';
    }

    /**
     * Establece los estilos de la hoja
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Datos de la hoja
     *
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        // Combinar celdas para el título y centrarlo
        $sheet->mergeCells('B5:D5');
        $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Registra los eventos de la hoja
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $user = User::where('id', auth()->user()->id)->toBase()->get()->first();
        $profileUser = Profile::where('user_id', $user->id)->first();
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $institution_logo = $institution->with('logo')->first();
        $logo1 = $institution_logo->logo->file ?? '';

        $data = [
            AfterSheet::class => function (AfterSheet $event) use ($logo1) {
                $logo = storage_path() . '/pictures/' . $logo1;
                if (file_exists($logo)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('institution_logo');
                    $drawing->setDescription('Logo Institucional');
                    $drawing->setPath($logo);
                    $drawing->setCoordinates('A1');
                    $drawing->setHeight(45);
                    $drawing->setWorksheet($event->sheet->getDelegate());
                }
            }
        ];
        return $data;
    }
}
