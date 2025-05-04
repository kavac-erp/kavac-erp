<?php

namespace Modules\Accounting\Exports;

use App\Models\Institution;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @class AccountingBalanceCheckUpSheetExport
 * @brief Gestiona la exportación de datos del reporte de balance de comprobación
 *
 * Realiza el proceso de exportación de datos del reporte de balance de comprobación
 *
 * @author Ing. Francisco Escala <fescala@cenditel.gob.ve> | <fjescala@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingBalanceCheckUpSheetExport implements WithDrawings, FromView, WithStyles
{
    /**
     * Listado de datos a exportar
     *
     * @var array $data
     */
    protected $data;

    /**
     * Método constructor de la clase
     *
     * @param array $view_data
     *
     * @return void
     */
    public function __construct(array $view_data)
    {
        $this->data = $view_data;
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
        $drawing->setDescription('This is my logo');
        $drawing->setPath(storage_path('pictures') . '/' . $institution->logo->file);
        $drawing->setHeight(90);
        $drawing->setOffsetX(90);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    /**
     * Retorna el archivo a exportar
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        return view('accounting::xlsx.checkup_balance', [
            'pdf' => $this->data['pdf'],
            'records' => $this->data['records'],
            'currency' => $this->data['currency'],
            'initDate' => $this->data['initDate'],
            'endDate' => $this->data['endDate'],
            'beginningBalance' => $this->data['beginningBalance'],
        ]);
    }

    /**
     * Establece los estilos del archivo a exportar
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     *
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        //$sheet->getStyle('B2')->getFont()->setSize(7);
        $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        //$sheet->getStyle('B3')->getFont()->setSize(7);
        $sheet->getStyle('A3:E3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        //$sheet->getStyle('B1')->getFont()->setSize(7);
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->mergeCells('C6:D6');
        $sheet->mergeCells('E6:F6');
        $sheet->mergeCells('G6:H6');
        $sheet->getStyle('A6:H6')->getAlignment()->setHorizontal('center');
    }
}
