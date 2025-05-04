<?php

namespace Modules\Warehouse\Exports;

use App\Models\MeasurementUnit;
use App\Models\HistoryTax;
use Modules\Warehouse\Models\WarehouseProduct;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class WarehouseProductExport extends \App\Exports\DataExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithEvents,
    WithCustomStartCell
{
    /**
     * Metodo para obtener la colección de datos a exportar
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return WarehouseProduct::all();
    }

    /**
     * Establece la celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string Celda de inicio de escritura
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        return [
            'Nombre del insumo',
            'Descripción del insumo',
            'Nombre de la unidad de medida',
            'Porcentaje del impuesto aplicado al insumo'
        ];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     object    $warehouseProduct    Objeto con las propiedades del modelo a exportar
     *
     * @return    array     Arreglo con los campos estrictamente a ser exportados
     */
    public function map($warehouseProduct): array
    {
        return [
            $warehouseProduct->name,
            htmlspecialchars_decode(strip_tags($warehouseProduct->description)),
            $warehouseProduct->measurementUnit->name,
            $warehouseProduct->tax_id ? $warehouseProduct->tax->histories[0]->percentage . ' %' : ''
        ];
    }

    /**
     * Registro de eventos al exportar datos
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $events = [
            AfterSheet::class => function (AfterSheet $event) {
                // Obtener la hoja de cálculo
                $sheet = $event->sheet;

                // Establecer opciones de selección
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Error en datos');
                $validation->setError('Debe seleccionar un dato de la lista');
                $validation->setPrompt('Seleccione un elemento de la lista');
                $records = $this->getArraysSelect();

                /* Identificador de la unidad de medida */
                $validation->setPromptTitle('Identificador de la unidad de medida');
                $validation->setFormula1(json_encode($records['measurementUnit'], JSON_UNESCAPED_UNICODE));
                $sheet->setDataValidation('C2:C100000', clone $validation);

                /* Porcentaje del impuesto aplicado al insumo */
                $validation->setPromptTitle('Porcentaje del impuesto aplicado al insumo');
                $validation->setFormula1(json_encode($records['tax'], JSON_UNESCAPED_UNICODE));
                $sheet->setDataValidation('D2:D100000', clone $validation);


                /* Definicion de estilos de la cabecera */
                $styleArray = [
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $sheet->getDelegate()->getStyle('A1:G1')->applyFromArray($styleArray);
            },
        ];
        return $events;
    }

    /**
     * Obtiene los valores de los selectores
     *
     * @return array
     */
    public function getArraysSelect(): array
    {
        $measurementUnit = template_choices(MeasurementUnit::class, ['name'], '', false);
        $measurementUnit = array_map(function ($field) {
            if ($field != 'Seleccione...') {
                return str_replace(array(' ', '__'), '_', str_replace(array(',', '.', '-'), '', $field));
            }
        }, $measurementUnit);
        $measurementUnitFormated = implode(',', $measurementUnit);
        $hTax = HistoryTax::query()->get()->map(function ($history) {
            return [
                'id' => $history->id,
                'name' => $history->tax->name . ' ' . $history->percentage,
            ];
        })->pluck('name', 'id')->toArray();


        $hTax = array_merge(['' => null], $hTax);
        $taxFormated = implode(',', $hTax);
        return [
            'measurementUnit' => $measurementUnitFormated,
            'tax' => $taxFormated,
        ];
    }
}
