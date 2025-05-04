<?php

namespace Modules\Finance\Exports;

use App\Models\Institution;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use App\Models\FiscalYear;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @class FinanceIvaExport
 * @brief Gestiona la exportación de datos de la tabla de finance_banks
 *
 * Clase para gestionar la exportación de datos de la tabla de finance_banks
 *
 * @author Ing. Francisco Escala <fescala@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceIvaExport implements
    WithDrawings,
    FromView,
    WithStyles
{
    /**
     * Parámetro de la ejecución de pago
     * @var string|array $params
     */
    protected $params;

    /**
     * Deducciones en la ejecución de pago
     *
     * @var string|array $deduction
     */
    protected $deduction;

    /**
     * Método constructor de la clase.
     *
     * @param string|array $financePaymentExecute Ejecución de pago
     * @param string|array $deduction Deducción
     *
     * @return void
     */
    public function __construct($financePaymentExecute = '', $deduction = '')
    {
        $this->params = $financePaymentExecute;
        $this->deduction = $deduction;
    }

    /**
     * Instancia de drawing para incorporar imágenes
     *
     * @return Drawing
     */
    public function drawings()
    {
        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('logo de la institución');
        $drawing->setPath(storage_path('pictures') . '/' . $institution->logo->file);
        $drawing->setHeight(90);
        $drawing->setCoordinates('B3');

        return $drawing;
    }

    /**
     * Muestra el archivo a exportar
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $institution['addressParse'] = strip_tags($institution->legal_address);
        $finance = $this->params;
        $deductions = $this->deduction;
        $url = "/images/logo.png";
                $currentFiscalYear = FiscalYear::query()
            ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();
        $date = Carbon::now();
        $dateShow = $date->format('d-m-Y');
        $month = $date->format('m');
        // Se Divide el texto en palabras
        $texto = $institution['addressParse'];

        $palabras = explode(' ', $texto);

        // Calculamos la longitud total del texto
        $longitudTotal = strlen($texto);

        // Calculamos la longitud aproximada para cada parte
        $longitudParte = ceil($longitudTotal / 3);

        // Inicializamos las partes
        $partes = [];
        $parteActual = '';

        // Iteramos sobre las palabras
        foreach ($palabras as $palabra) {
            // Agregamos la palabra actual a la parte actual
            $parteActual .= $palabra . ' ';

            // Si la longitud de la parte actual supera o iguala la longitud deseada
            if (strlen($parteActual) >= $longitudParte) {
                // Agregamos la parte actual al array de partes
                $partes[] = trim($parteActual);

                // Reiniciamos la parte actual
                $parteActual = '';
            }
        }

        // Si queda alguna parte no agregada
        if (!empty($parteActual)) {
            $partes[] = trim($parteActual);
        }
        $institution['addressParse'] = $partes;

        return view(
            'finance::payments_execute.report-iva',
            compact(
                'institution',
                'month',
                'dateShow',
                'currentFiscalYear',
                'url',
                'deductions',
                'finance'
            )
        );
    }

    /**
     * Aplica los estilos de las filas, columnas y celdas, del archivo a exportar
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     *
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        // Aplicar estilos al rango de celdas que necesitas
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(4);
        $sheet->getColumnDimension('C')->setWidth(4);
        $sheet->getStyle('A2')->getFont()->setBold(true); // Hacer el texto en A2 en negrita
        $sheet->getStyle('G4')->getFont()->setBold(true); // Hacer el texto en A2 en negrita
        $sheet->mergeCells('G3:L3');
        $sheet->getStyle('G3:L3')->getBorders()->getAllBorders()->setBorderStyle('thin'); // Borde para la fila de encabezado

        // Centrar el texto en las celdas fusionadas
        $sheet->getStyle('G3:L3')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('G4:L4');
        $sheet->getStyle('G4:L4')->getAlignment()->setHorizontal('center');
        // Fusionar celdas desde M5 hasta N5
        $sheet->mergeCells('F5:L5');
        $sheet->getStyle('F5')->getFont()->setBold(true);
        $sheet->getStyle('F5')->getFont()->setSize(7);
        $sheet->getStyle('F5:L5')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('M5:N5');
        $sheet->getStyle('M5')->getFont()->setBold(true);
        $sheet->getStyle('M5')->getFont()->setSize(6);

        $sheet->getStyle('M5:N5')->getAlignment()->setHorizontal('center');
        // Agregar bordes izquierdo, derecho y arriba a las celdas combinadas
        $sheet->getStyle('M5')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N5')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('M5:N5')->getBorders()->getTop()->setBorderStyle('thin');

        $sheet->getStyle('P5')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P5')->getFont()->setBold(true);
        $sheet->getStyle('P5')->getFont()->setSize(6);

        // Agregar bordes izquierdo, derecho y arriba a las celdas combinadas
        $sheet->getStyle('P5')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P5')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P5')->getBorders()->getTop()->setBorderStyle('thin');
        //columna 6
        $sheet->mergeCells('F6:L6');
        $sheet->getStyle('F6')->getFont()->setBold(true);
        $sheet->getStyle('F6')->getFont()->setSize(7);
        $sheet->getStyle('F6:L6')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('M6:N6');
        $sheet->getStyle('P6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P6')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P6')->getBorders()->getRight()->setBorderStyle('thin');

        // Agregar bordes izquierdo, derecho y arriba a las celdas combinadas
        $sheet->getStyle('M6')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N6')->getBorders()->getRight()->setBorderStyle('thin');
        //columna 7
        $sheet->mergeCells('F7:L7');
        $sheet->getStyle('F7')->getFont()->setBold(true);
        $sheet->getStyle('F7')->getFont()->setSize(7);
        $sheet->getStyle('F7:L7')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('M7:N7');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P7')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P7')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P7')->getFont()->setBold(true);
        $sheet->getStyle('P7')->getFont()->setSize(6);
        // Agregar bordes izquierdo, derecho y arriba a las celdas combinadas
        $sheet->mergeCells('M7:N7');
        $sheet->getStyle('M7')->getFont()->setBold(true);
        $sheet->getStyle('M7')->getFont()->setSize(6);

        $sheet->getStyle('M7:N7')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('M7')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N7')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('M7')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('P7')->getBorders()->getBottom()->setBorderStyle('thin');
        //columna 9
        $sheet->mergeCells('E9:J9');
        $sheet->getStyle('E9')->getFont()->setSize(7);
        $sheet->getStyle('E9')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J9')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E9')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('E9:J9')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('J9')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->mergeCells('K9:L9');
        $sheet->getStyle('K9')->getFont()->setSize(7);
        $sheet->getStyle('K9')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L9')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K9')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('K9:L9')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('L9')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->mergeCells('N9:P9');
        $sheet->getStyle('N9:P9')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('N9')->getFont()->setSize(7);
        $sheet->getStyle('N9')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P9')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('N9')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('N9:P9')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('P9')->getBorders()->getBottom()->setBorderStyle('thin');
        //columna 10

        $sheet->mergeCells('E10:J10');
        $sheet->getStyle('E10')->getFont()->setBold(true);
        $sheet->getStyle('E10:J10')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E10')->getFont()->setSize(11);
        $sheet->getStyle('E10')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J10')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E10:J10')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->mergeCells('K10:L10');
        $sheet->getStyle('K10')->getFont()->setSize(7);
        $sheet->getStyle('K10')->getFont()->setBold(true);
        $sheet->getStyle('E10:J10')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E10:J10')->getFont()->setBold(true);
        $sheet->getStyle('K10')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L10')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K10:L10')->getBorders()->getTop()->setBorderStyle('thin');

        $sheet->getStyle('N10:P10')->getFont()->setBold(true);
        $sheet->getStyle('N10:P10')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('N10')->getFont()->setSize(7);
        $sheet->getStyle('P10')->getFont()->setSize(7);
        $sheet->getStyle('N10')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P10')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('N10:P10')->getBorders()->getTop()->setBorderStyle('thin');
        //columna 11
        $sheet->mergeCells('E11:J11');
        $sheet->getStyle('E11')->getFont()->setBold(true);
        $sheet->getStyle('E11')->getFont()->setSize(11);
        $sheet->getStyle('E11')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J11')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E11')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('J11')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('E11:J11')->getBorders()->getBottom()->setBorderStyle('thin');

        $sheet->mergeCells('K11:L11');
        $sheet->getStyle('K11')->getFont()->setSize(7);
        $sheet->getStyle('K11')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L11')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K11')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('L11')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('N11:P11')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('N11')->getFont()->setSize(7);
        $sheet->getStyle('P11')->getFont()->setSize(7);
        $sheet->getStyle('N11')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P11')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('N11:P11')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('P11')->getBorders()->getBottom()->setBorderStyle('thin');
        //columna 13
        $sheet->mergeCells('E13:L13');
        $sheet->getStyle('E13')->getFont()->setBold(true);
        $sheet->getStyle('E13:L13')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E13')->getFont()->setSize(11);
        $sheet->getStyle('E13:L13')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('E13:L13')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('E13')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L13')->getBorders()->getRight()->setBorderStyle('thin');

        //columna 14
        // Fusionar las celdas E14:L14
        $sheet->mergeCells('E14:L14');

        // Fusionar las celdas E15:L15
        $sheet->mergeCells('E15:L15');

        // Fusionar las celdas E16:L16
        $sheet->mergeCells('E16:L16');

       // $sheet->mergeCells('E14:L16');
        $sheet->getStyle('E14')->getFont()->setBold(true);
        $sheet->getStyle('E14:L14')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E14:E16')->getFont()->setSize(10);
        $sheet->getStyle('E14:L14')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('E14')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L14')->getBorders()->getRight()->setBorderStyle('thin');

        //columna 15
        $sheet->mergeCells('E15:L15');
        $sheet->getStyle('E15')->getFont()->setBold(true);
        $sheet->getStyle('E15:L15')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E15')->getFont()->setSize(10);
        $sheet->getStyle('E15')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L15')->getBorders()->getRight()->setBorderStyle('thin');

        //columna 16
        $sheet->mergeCells('E16:L16');
        $sheet->getStyle('E16')->getFont()->setBold(true);
        $sheet->getStyle('E16:L16')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E16')->getFont()->setSize(10);
        $sheet->getStyle('E16')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L16')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E16:L16')->getBorders()->getBottom()->setBorderStyle('thin');

        //columna 17
        //columna 18
        $sheet->mergeCells('E18:J18');
        $sheet->getStyle('E18')->getFont()->setSize(7);
        $sheet->getStyle('E18')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J18')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E18')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('E18:J18')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('J18')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->mergeCells('K18:L18');
        $sheet->getStyle('K18')->getFont()->setSize(7);
        $sheet->getStyle('K18')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L18')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K18')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('K18:L18')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('L18')->getBorders()->getBottom()->setBorderStyle('thin');
        //columna 19
        $sheet->mergeCells('E19:J19');
        $sheet->getStyle('E19')->getFont()->setBold(true);
        $sheet->getStyle('E19:J19')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E19')->getFont()->setSize(11);
        $sheet->getStyle('E19')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J19')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E19:J19')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('E19:J19')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->mergeCells('K19:L19');
        $sheet->getStyle('K19')->getFont()->setSize(7);
        $sheet->getStyle('K19')->getFont()->setBold(true);
        $sheet->getStyle('E19:J19')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E19:J19')->getFont()->setBold(true);
        $sheet->getStyle('K19')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L19')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K19:L19')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('K19:L19')->getBorders()->getBottom()->setBorderStyle('thin');
        //columna 20
        //columna 21
        $sheet->mergeCells('L21:N21');
        $sheet->getStyle('L21:N21')->getFont()->setBold(true);
        $sheet->getStyle('L21:N21')->getFont()->setSize(7);
        $sheet->getStyle('L21:N21')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('L21:N21')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('L21')->getFont()->setSize(11);
        $sheet->getStyle('L21')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N21')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('L21:N21')->getBorders()->getTop()->setBorderStyle('thin');
        //columna 22
        $sheet->mergeCells('L22:N22');
        $sheet->getStyle('L22:N22')->getFont()->setBold(true);
        $sheet->getStyle('L22:N22')->getFont()->setSize(7);
        $sheet->getStyle('L22:N22')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('L22:N22')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('L22')->getFont()->setSize(11);
        $sheet->getStyle('L22')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N22')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('L22:N22')->getBorders()->getBottom()->setBorderStyle('thin');
        //columna 23
        $sheet->getStyle('E23:N23')->getFont()->setBold(true);
        $sheet->getStyle('E23:N23')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('E23:N23')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E23:N23')->getFont()->setSize(8);
        $sheet->getStyle('K23')->getFont()->setSize(7);
        $sheet->getStyle('E23:N23')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('E23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('E23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('F23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('F23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('G23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('G23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('H23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('H23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('I23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('I23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('J23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('K23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('L23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('M23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('M23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('N23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P23')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P23')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P23')->getFont()->setBold(true);
        $sheet->getStyle('P23')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('P23')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P23')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('P23')->getFont()->setSize(9);

        //columna 24
        $sheet->getStyle('E24:N24')->getFont()->setBold(true);
        $sheet->getStyle('E24:N24')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('E24:N24')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E24:N24')->getFont()->setSize(8);
        $sheet->getStyle('J24')->getFont()->setSize(7);
        $sheet->getStyle('E24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('E24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('F24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('F24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('G24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('G24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('H24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('H24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('I24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('I24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('J24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('K24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('L24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('M24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('M24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('N24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P24')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P24')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P24')->getFont()->setBold(true);
        $sheet->getStyle('P24')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('P24')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P24')->getFont()->setSize(9);
        //columna 25
        $sheet->getStyle('E26:P26')->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('E25:N25')->getFont()->setBold(true);
        $sheet->getStyle('E25:N25')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('E25:N25')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E25:N25')->getFont()->setSize(8);
        $sheet->getStyle('I25')->getFont()->setSize(7);
        $sheet->getStyle('K25')->getFont()->setSize(7);
        $sheet->getStyle('E25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('E25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('F25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('F25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('G25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('G25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('H25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('H25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('I25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('I25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('J25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('J25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('K25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('K25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('L25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('L25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('M25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('M25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('N25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('N25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P25')->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P25')->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P25')->getFont()->setBold(true);
        $sheet->getStyle('P25')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => 'D9D9D9'],]);
        $sheet->getStyle('P25')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P25')->getFont()->setSize(9);
        $deductions = $this->deduction;
        $line = 26;
        foreach ($this->deduction as $key => $value) {
            $line = $key + 1 + 25;
            $sheet->getStyle('E' . $line . ':P' . $line)->getBorders()->getTop()->setBorderStyle('thin');
            $sheet->getStyle('E' . $line . ':N' . $line)->getFont()->setBold(true);
            $sheet->getStyle('E' . $line . ':N' . $line)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('E' . $line . ':N' . $line)->getFont()->setSize(9);
            $sheet->getStyle('E' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('E' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('F' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('F' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('G' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('G' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('H' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('H' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('I' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('I' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('J' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('J' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('K' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('K' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('L' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('L' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('M' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('M' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('N' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('N' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('P' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
            $sheet->getStyle('P' . $line)->getBorders()->getRight()->setBorderStyle('thin');
            $sheet->getStyle('P' . $line)->getFont()->setBold(true);
            $sheet->getStyle('P' . $line)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('P' . $line)->getFont()->setSize(9);
        }
        $line += 1;
        $sheet->getStyle('E' . $line . ':P' . $line)->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('P' . $line)->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('P' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('P' . $line)->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('P' . $line)->getFont()->setBold(true);
        $sheet->getStyle('P' . $line)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('P' . $line)->getFont()->setSize(9);
        $line += 3;
        $sheet->mergeCells('E' . $line . ':I' . $line);
        $sheet->getStyle('E' . $line . ':I' . $line)->getBorders()->getTop()->setBorderStyle('thin');
        $sheet->getStyle('E' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('I' . $line)->getBorders()->getRight()->setBorderStyle('thin');
        $line += 1;
        $sheet->mergeCells('E' . $line . ':I' . $line);
        $sheet->getStyle('E' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('E' . $line . ':I' . $line)->getFont()->setBold(true);
        $sheet->getStyle('E' . $line . ':I' . $line)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('I' . $line)->getBorders()->getRight()->setBorderStyle('thin');
        $line += 1;
        $sheet->mergeCells('E' . $line . ':I' . $line);
        $sheet->getStyle('E' . $line . ':I' . $line)->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getStyle('E' . $line)->getBorders()->getLeft()->setBorderStyle('thin');
        $sheet->getStyle('I' . $line)->getBorders()->getRight()->setBorderStyle('thin');
        $sheet->getStyle('E' . $line . ':I' . $line)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E' . $line . ':I' . $line)->getFont()->setBold(true);
        $sheet->getStyle('I' . $line)->getBorders()->getRight()->setBorderStyle('thin');

        //columna 22
        //columna 22

        // Se pueden ajustar y agregar más estilos según las necesidades
    }
}
