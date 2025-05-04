<?php

namespace Modules\Payroll\Exports;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * @class PayrollReportStaffsExport
 * @brief Clase que exporta el listado de personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollReportStaffsExport implements
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    FromCollection,
    ShouldQueue,
    WithEvents
{
    use Exportable;

    /**
     * Registros a exportar
     *
     * @var array $records
     */
    protected $records;

    /**
     * Índices de los registros a exportar
     *
     * @var array $keys
     */
    protected $keys;

    /**
     * Encabezados de las columnas
     *
     * @var array $headings
     */
    protected $headings;

    /**
     * Número máximo de registros a exportar
     * @var integer $maxChildCount
     */
    protected $maxChildCount;

    /**
     * Método constructor de la clase
     *
     * @param mixed $columns
     *
     * @return void
     */
    public function __construct(protected $columns)
    {
        $arrayHeadings = [
            'Numeración',
            'Trabajador',
            'Número de cédula',
            'Cargo',
            'Fecha de ingreso a la institución',
            'Años en la institución'
        ];

        $additionalHeadings = [
            'payroll_gender' => 'Género',
            'payroll_disability' => 'Discapacidad',
            'has_driver_license' => 'Licencia',
            'payroll_blood_type' => 'Tipo de sangre',
            'payroll_age' => 'Edad',
            'payroll_instruction_degree' => 'Grado de instrucción',
            'payroll_professions' => 'Profesión',
            'payroll_study' => '¿Estudia?',
            'marital_status' => 'Estado civil',
            'payroll_childs' => 'Hijos',
            'payroll_is_active' => 'Activo',
            'payroll_inactivity_types' => 'Tipo de inactividad',
            'payroll_position_types' => 'Tipo de cargo',
            'payroll_positions' => 'Cargo',
            'payroll_staff_types' => 'Tipo de personal',
            'payroll_contract_types' => 'Tipo de contrato',
            'departments' => 'Departamento',
            'time_service' => 'Total años de servicio'
        ];

        foreach ($additionalHeadings as $column => $heading) {
            if ($columns[$column] != false) {
                $arrayHeadings[] = $heading;
            }
        }

        $this->headings = $arrayHeadings;
    }

    /**
     * Colección de datos a exportar
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $rowNumber = 0;

        $collection = collect($this->records)->map(function ($row) use (&$rowNumber) {
            $rowNumber++;
            $row['index'] = $rowNumber;
            return $row;
        });

        return $collection;
    }

    /**
     * Encabezados de las columnas de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        $arrayHeadings = $this->headings;

        return $arrayHeadings;
    }

    /**
     * Mapeo de datos de la colección
     *
     * @param array $row Fila con datos a exportar
     *
     * @return array
     */
    public function map($row): array
    {
        $array = [];

        if (array_key_exists('payroll_staff', $row) && array_key_exists('start_date', $row) && array_key_exists('time_worked', $row)) {
            $array = [
                [
                    $row['index'],
                    $row['payroll_staff'],
                    $row['payroll_id_number'],
                    $row['payroll_position'],
                    $row['start_date'],
                    $row['time_worked']
                ]
            ];

            if ($this->keys['conditions']['payroll_gender'] != false) {
                array_push($array[0], $row['payroll_gender']);
            }
            if ($this->keys['conditions']['payroll_disability'] != false) {
                array_push($array[0], $row['payroll_disability']);
            }
            if ($this->keys['conditions']['has_driver_license'] != false) {
                array_push($array[0], $row['payroll_license']);
            }
            if ($this->keys['conditions']['payroll_blood_type'] != false) {
                array_push($array[0], $row['payroll_blood_type']);
            }
            if ($this->keys['conditions']['payroll_age'] != false) {
                array_push($array[0], $row['payroll_age']);
            }
            if ($this->keys['conditions']['payroll_instruction_degree'] != false) {
                array_push($array[0], $row['payroll_instruction_degree']);
            }
            if ($this->keys['conditions']['payroll_professions'] != false) {
                array_push($array[0], $row['payroll_profession']);
            }
            if ($this->keys['conditions']['payroll_study'] != false) {
                array_push($array[0], $row['payroll_study']);
            }
            if ($this->keys['conditions']['marital_status'] != false) {
                array_push($array[0], $row['payroll_marital_status']);
            }
            if ($this->keys['conditions']['payroll_childs'] != false) {
                $childrenCount = 0;
                foreach ($row['payroll_childs_arrays'] as $child) {
                    if (
                        isset($child['payroll_relationship']) && $child['payroll_relationship'] &&
                        strpos($child['payroll_relationship']['name'], 'Hijo') !== false
                    ) {
                        $child_name = $child['first_name'] . ' ' . $child['last_name'];
                        $birth = new DateTime($child['birthdate']);
                        $now = new DateTime(date("Y-m-d"));
                        $difference = $now->diff($birth);
                        $age = $difference->format("%y");
                        $level = '';
                        if ($child['payroll_schooling_level_id'] !== null) {
                            foreach ($row['schooling_levels'] as $schooling_level) {
                                if ($schooling_level['id'] == $child['payroll_schooling_level_id']) {
                                    $level = $schooling_level['text'];
                                }
                            }
                        }
                        $childrenCount++;
                    }
                }

                foreach ($row['payroll_childs_arrays'] as $child) {
                    if (
                        isset($child['payroll_relationship']) && $child['payroll_relationship'] &&
                        strpos($child['payroll_relationship']['name'], 'Hijo') !== false
                    ) {
                        $child_name = $child['first_name'] . ' ' . $child['last_name'];
                        $birth = new DateTime($child['birthdate']);
                        $now = new DateTime(date("Y-m-d"));
                        $difference = $now->diff($birth);
                        $age = $difference->format("%y");
                        $level = '';
                        if ($child['payroll_schooling_level_id'] !== null) {
                            foreach ($row['schooling_levels'] as $schooling_level) {
                                if ($schooling_level['id'] == $child['payroll_schooling_level_id']) {
                                    $level = $schooling_level['text'];
                                }
                            }
                        }
                        $children = 'Nombre: ' . $child_name . ', ' . 'Edad: ' . $age . ', ' . ' Escolaridad: ' . $level;

                        array_push($array[0], $children);
                    }
                }

                for ($i = 0; $i < $this->maxChildCount - $childrenCount; $i++) {
                    array_push($array[0], '');
                }
            }
            if ($this->keys['conditions']['payroll_is_active'] != false) {
                array_push($array[0], $row['payroll_is_active']);
            }
            if ($this->keys['conditions']['payroll_inactivity_types'] != false) {
                array_push($array[0], $row['payroll_inactivity_type']);
            }
            if ($this->keys['conditions']['payroll_position_types'] != false) {
                array_push($array[0], $row['payroll_position_type']);
            }
            if ($this->keys['conditions']['payroll_positions'] != false) {
                array_push($array[0], $row['payroll_position']);
            }
            if ($this->keys['conditions']['payroll_staff_types'] != false) {
                array_push($array[0], $row['payroll_staff_type']);
            }
            if ($this->keys['conditions']['payroll_contract_types'] != false) {
                array_push($array[0], $row['payroll_contract_type']);
            }
            if ($this->keys['conditions']['departments'] != false) {
                array_push($array[0], $row['department']);
            }
            if ($this->keys['conditions']['time_service'] != false) {
                array_push($array[0], $row['time_service']);
            }
        }

        return $array;
    }

    /**
     * Registra los eventos de la hoja
     *
     * @return array
     */
    public function registerEvents(): array
    {
        $headings = $this->headings;
        $records = $this->records;
        if (in_array('Hijos', $headings)) {
            $data = [
                AfterSheet::class => function (AfterSheet $event) use ($records, $headings) {
                    $sheet = $event->sheet;
                    $counts = 0;
                    $maxChildCount = 0;
                    $head = 0;

                    foreach ($headings as $heading) {
                        $head++;
                        if ($heading == 'Hijos') {
                            break;
                        }
                    }

                    $letter = 64 + $head;

                    foreach ($records as $key => $children) {
                        if (count($children['payroll_childs_arrays']) > 0) {
                            foreach ($children['payroll_childs_arrays'] as $child) {
                                if (
                                    isset($child['payroll_relationship']) && $child['payroll_relationship'] &&
                                    strpos($child['payroll_relationship']['name'], 'Hijo') !== false
                                ) {
                                    $counts++;
                                }
                            }

                            if ($counts > $maxChildCount) {
                                $maxChildCount = $counts;
                            }

                            $counts = 0;
                        }
                    }

                    $letterD = $letter + $maxChildCount;

                    $sheet->mergeCells($this->setCellCharacter($letter) . '1:' . $this->setCellCharacter($letterD - 1) . '1');

                    $char = chr(90);

                    $cellRange = 'A1:' . $char . '1'; // All headers

                    $styleArray = [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                    ];

                    $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
                }
            ];

            return $data;
        } else {
            $data = [];
            return $data;
        }
    }


    /**
     * Establece la celda donde va a ser colocada la información
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param     integer|string    $letter  Número según el código ascii para el cáracter
     *
     * @return    string                     Carácter de la celda en la que se va a ubicar la información
     */
    public function setCellCharacter($letter)
    {
        $char = '';
        $count = 65;
        $countWhile = -1;
        $newLetter = $letter;
        while ($newLetter > 90) {
            $letterValue = intdiv($newLetter - $count, 26);
            $char = $char == '' ? chr($count) : chr($count + $letterValue + $countWhile);
            $newLetter = $newLetter - 26;
            $countWhile += 1;
        }

        if ($char == '') {
            $char = chr($newLetter);
        } else {
            $char = $char . chr($newLetter);
        }

        return $char;
    }
}
