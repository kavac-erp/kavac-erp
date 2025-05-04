<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @class DataExport
 *
 * @brief Permite la exportación de datos
 *
 * Permite la exportación de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DataExport implements FromCollection, WithHeadingRow
{
    use Exportable;

    /**
     * Método constructor de la clase
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  object|null  $model    Objeto con información del modelo para el cual se van a exportar los datos
     */
    public function __construct(protected mixed $model = null)
    {
        //
    }

    /**
     * Gestiona la colección de registros a exportar
     *
     * @author Ing. Roldan Vargas <roldandvg@gmail.com> | <rvargas@cenditel.gob.ve>
     *
     * @return void
     */
    public function collection()
    {
        return $this->model::all();
    }

    /**
     * Establece una validación de lista para un rango de celdas en una hoja de cálculo.
     *
     * @param Worksheet $sheet              La hoja de cálculo a la que se aplicará la validación.
     * @param string    $column             La letra de la columna a la que se aplicará la validación.
     * @param int       $initRow            El número de fila inicial a la que se aplicará la validación.
     * @param int|null  $endRow             El número de fila final a la que se aplicará la validación.
     *                                      Si es null, la validación se aplicará a todas las filas hasta
     *                                      la fila más alta en la hoja de cálculo o 5000, lo que sea menor.
     * @param string    $chunckName         El nombre del rango de validación.
     * @param string    $validationRange    El rango a validar.
     *
     * @return void
     */
    public function setFunctionList(
        Worksheet $sheet,
        string $column,
        int $initRow,
        ?int $endRow,
        string $chunckName,
        string $validationRange,
    ): void {
        $dataValidation = new DataValidation();
        $dataValidation->setType(DataValidation::TYPE_LIST);
        $dataValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $dataValidation->setAllowBlank(true);
        $dataValidation->setShowInputMessage(true);
        $dataValidation->setShowErrorMessage(true);
        $dataValidation->setShowDropDown(true);
        $dataValidation->setErrorTitle('Error en datos');
        $dataValidation->setError('Debe seleccionar un dato de la lista');
        $dataValidation->setPrompt('Seleccione un elemento de la lista');
        $dataValidation->setPromptTitle('Titulo del campo');
        $dataValidation->setFormula1($chunckName);

        for ($i = $initRow; $i <= ($endRow ?? max($sheet->getHighestRow(), 5000)); $i++) {
            $sheet->getCell(strtoupper($column) . $i)->setDataValidation($dataValidation);
        }
        $sheet->getParent()->addNamedRange(new NamedRange($chunckName, $sheet, $validationRange));
    }
}
