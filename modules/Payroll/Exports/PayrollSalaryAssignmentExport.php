<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;        # Agregar Cabecera
use Maatwebsite\Excel\Concerns\ShouldAutoSize;      # AutoEspaciar Columnas
use Maatwebsite\Excel\Concerns\WithEvents;          # Registrar Evento
use Maatwebsite\Excel\Events\AfterSheet;            # Modificar Fuentes y Tamaño
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

# Indicar la celda en la que debe comenzar (solo FromCollection)

/**
 * @class PayrollSalaryAssignmentExport
 * @brief Clase que gestiona los objetos exportados del modelo de asignaciones salariales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAssignmentExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithEvents,
    WithCustomStartCell
{
    /**
     * Identificador único de la asignación que se desea exportar
     *
     * @var integer $salary_assignment_id
     */
    private $salary_assignment_id;

    /**
     * Establece el identificador de la asignación salarial
     *
     * @param integer $salary_assignment_id Identificador único de la asignación que se desea exportar
     */
    public function setSalaryAssignmentId(int $id)
    {
        $this->salary_assignment_id = $id;
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'B2';
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // @todo Verificar la exisencia del modelo PayrollSalaryAssignment
        //return PayrollSalaryAssignment::all();

        return collect([]);
    }

    /**
     * Establece la cabecera del archivo exportado
     *
     * @return array Arreglo que contiene la estructura de cabecera del archivo exportado
     */
    public function headings(): array
    {
        return ['#','Name','Email'];
    }

    /**
     * Registra los eventos de la clase
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $cellRange = 'A1:W1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }
}
