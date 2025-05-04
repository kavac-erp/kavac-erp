<?php

/** Gestiona la exportación de datos */

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
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DataExport implements FromCollection, WithHeadingRow
{
    use Exportable;

    /**
     * Método constructor de la clase
     *
     * @method    __construct(object $model)
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  object|null  $model    Objeto con información del modelo para el cual se van a exportar los datos
     */
    public function __construct(protected mixed $model = null)
    {
    }

    /**
     * Gestiona la colección de registros a exportar
     *
     * @method    collection()
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function collection()
    {
        return $this->model::all();
    }

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
