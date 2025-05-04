<?php

namespace Modules\Payroll\Exports\Sheets;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

/**
 * @class PayrollSalaryTabulatorsSheet
 *
 * Clase que gestiona los objetos exportados del modelo de tabuladores salariales como hoja anexa al reporte de nómina
 *
 * @author Francisco J. P. Ruiz <javierrupe19@gmail.com | fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryTabulatorsSheet implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithTitle,
    WithStyles,
    WithEvents,
    WithCustomStartCell
{
    /**
     * Tabulador salarial
     *
     * @var  array $payrollSalaryTabulator
     */
    protected $payrollSalaryTabulator;

    /**
     * Información del salario
     *
     * @var array $info
     */
    protected $info;

    /**
     * Encabezado de la hoja
     *
     * @var array $headers
     */
    protected $headers;

    /**
     * Título de la hoja
     *
     * @var string $title
     */
    protected $title;

    /**
     * Método constructor de la clase.
     *
     * @param array $payrollSalaryTabulator Arreglo con la información del tabulador salarial
     *
     * @return void
     */
    public function __construct(array $payrollSalaryTabulator = [])
    {
        $this->info = array_pop($payrollSalaryTabulator);
        $this->headers = array_pop($payrollSalaryTabulator);
        $this->payrollSalaryTabulator = $payrollSalaryTabulator;
        $this->title = $this->info['name'] . ' ' . $this->info['code'];
    }

    /**
     * Establece el título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
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
     * Colección de datos a exportar
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function collection(): Collection
    {
        return new Collection($this->payrollSalaryTabulator);
    }

    /**
     * Establece la cabecera del archivo exportado
     *
     * @return array Arreglo que contiene la estructura de cabecera del archivo exportado
     */
    public function headings(): array
    {
        return [
            ['text' => $this->title],
            [''],
            $this->headers
        ];
    }

    /**
     * Estilos de la hoja
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Objeto con los datos de la hoja
     *
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        // Combinar celdas para el título y centrarlo
        $sheet->mergeCells('B5:' . $sheet->getHighestDataColumn() . '5');
        $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Registro de eventos de la hoja
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $payrollSalaryTabulator = PayrollSalaryTabulator::where('id', $this->info['id'])
            ->with(['institution' => function ($query) {
                $query->with('logo')->get();
            }])->first();
        $payrollSalaryTabulator_logo = $payrollSalaryTabulator->institution->logo->file ?? '';
        $data = [
            AfterSheet::class => function (AfterSheet $event) use ($payrollSalaryTabulator_logo) {
                $logo = storage_path() . '/pictures/' . $payrollSalaryTabulator_logo;
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
