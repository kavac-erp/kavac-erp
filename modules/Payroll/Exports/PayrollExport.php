<?php

namespace Modules\Payroll\Exports;

use App\Models\User;
use App\Models\Profile;
use App\Models\Parameter;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Institution;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Modules\Payroll\Models\PayrollConceptType;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Modules\Payroll\Exports\Sheets\PayrollConceptsSheet;
use Modules\Payroll\Exports\Sheets\PayrollSalaryTabulatorsSheet;

/**
 * @class PayrollExport
 * @brief Clase que exporta la nómina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithMapping,
    WithEvents,
    WithTitle,
    WithCustomStartCell,
    WithMultipleSheets
{
    use Exportable;

    /**
     * Identificador de la nómina
     *
     * @var integer $payrollId
     */
    protected $payrollId;

    /**
     * Datos de la nómina
     *
     * @var Payroll $payroll
     */
    protected $payroll;

    /**
     * Datos del modelo

     * @var PayrollStaffPayroll $model
     */
    protected $model;

    /**
     * Total de los montos en nómina a exportar

     * @var array $total
     */
    protected $total;

    /**
     * Número de decimales a mostrar en el archivo
     *
     * @var Parameter $number_decimals
     */
    protected $number_decimals;

    /**
     * Función a implementar para redondear los montos
     *
     * @var Parameter $round
     */
    protected $round;

    /**
     * Mostrar conceptos en ceros
     *
     * @var Parameter $zero_concept
     */
    protected $zero_concept;

    /**
     * Título del archivo
     *
     * @var string $title
     */
    protected $title;

    /**
     * Establece el nombre del archivo a exportar
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Retorna un array de las hojas a exportar
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            'Payroll' => $this,
            'Concepts' => new PayrollConceptsSheet($this->payroll->concept_types)
        ];

        foreach ($this->payroll->salary_tabulators as $key => $payrollSalaryTabulator) {
            $sheets['Tabulator' . $key] = new PayrollSalaryTabulatorsSheet($payrollSalaryTabulator);
        }

        return $sheets;
    }

    /**
     * Establece el identificador del registro de nómina
     *
     * @param integer $payrollId Identificador único del tabuldor salarial
     *
     * @return void
     */
    public function setPayrollId(int $payrollId)
    {
        $this->payroll = Payroll::findOrFail($payrollId);
        $this->title = 'Nómina - ' . $this->payroll->name;
        $this->payrollId = $payrollId;
        $this->model = $this->payroll->payrollStaffPayrolls;
        $this->total = [];
        $this->number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
        $this->round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
        $this->zero_concept = Parameter::where('p_key', 'zero_concept')->where('required_by', 'payroll')->first();
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
    * @return    \Illuminate\Support\Collection
    */
    public function collection()
    {
        $payrollRegister = Payroll::query()
            ->with('payrollStaffPayrolls.payrollStaff')
            ->find($this->payrollId);

        $filteredRecords = $payrollRegister->payrollStaffPayrolls
            ->filter(function ($record) {
                return isset($record->payrollStaff);
            })
            ->sortBy(function ($record) {
                return $record->payrollStaff->first_name . ' ' . $record->payrollStaff->last_name;
            })
            ->values()
            ->map(function ($record, $index) {
                $record['index'] = $index + 1;
                return $record;
            });

        return $filteredRecords;
    }

    /**
     * Establece las cabeceras de los datos en el archivo a exportar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return    array    Arreglo con las cabeceras de los datos a exportar
     */
    public function headings(): array
    {
        $records = $this->model;
        $values = [];
        foreach ($records as $record) {
            foreach ($record['concept_type'] as $key => $conceptType) {
                if (!array_key_exists($key, $values)) {
                    $values[$key] = $conceptType;
                } else {
                    $values[$key] += $conceptType;
                }
            }
        }
        $concepTypes = $values;

        $signsOrder = ['+', '-', 'NA'];
        $signIndex = 0;
        $data = [];

        while (1) {
            foreach ($concepTypes as $key => $concepType) {
                $payrollConceptType = PayrollConceptType::where('name', $key)->first();

                if (isset($payrollConceptType)) {
                    if ($payrollConceptType->sign == $signsOrder[$signIndex]) {
                        $data[$key] = $concepType;
                        unset($concepTypes[$key]);
                    }
                } else {
                    unset($concepTypes[$key]);
                }
            }

            $signIndex++;

            if (count($concepTypes) == 0) {
                break;
            }
        }

        $concepTypes = $data;

        $headings = [' ', 'Trabajadores', 'C.I.', 'Grado de instrucción', 'Cargo', 'Fecha de ingreso', 'Total años de servicio'];
        $subHeadings = [' ',' ',' ',' ',' ',' ',' '];
        $flagSign = '';

        foreach ($concepTypes as $key => $conceptType) {
            if (count($conceptType) > 0) {
                foreach ($conceptType as $type) {
                    if ($flagSign != $type['sign'] && $type['sign'] == 'NA') {
                        array_push($subHeadings, ' ');
                        array_push($subHeadings, ' ');
                    }
                    array_push($subHeadings, $type['name']);
                    $flagSign = $type['sign'];
                }

                if ($flagSign == 'NA') {
                    array_push($subHeadings, ' ');
                    array_push($subHeadings, ' ');
                } else {
                    array_push($subHeadings, ' ');
                }
            }
        }

        return [
            $headings,
            $subHeadings,
        ];
    }

    /**
     * Establece las columnas que van a ser exportadas
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param     object    $payroll          Objeto con las propiedades del modelo a exportar
     *
     * @return    array                       Arreglo con los campos estrictamente a ser exportados
     */
    public function map($payroll): array
    {
        $nameDecimalFunction = $this->round->p_value == 'false' ? 'currency_format' : 'round';
        $records = $this->model;
        $values = [];
        foreach ($records as $record) {
            foreach ($record['concept_type'] as $key => $conceptType) {
                if (!array_key_exists($key, $values)) {
                    $values[$key] = $conceptType;
                } else {
                    $values[$key] += $conceptType;
                }
            }
        }

        $largestConcepType = $values;
        $signsOrder = ['+', '-', 'NA'];
        $signIndex = 0;
        $sortedData = [];

        while (1) {
            foreach ($largestConcepType as $key => $concepType) {
                $payrollConceptType = PayrollConceptType::where('name', $key)->first();

                if (isset($payrollConceptType)) {
                    if ($payrollConceptType->sign == $signsOrder[$signIndex]) {
                        $sortedData[$key] = $concepType;
                        unset($largestConcepType[$key]);
                    }
                } else {
                    unset($largestConcepType[$key]);
                }
            }

            $signIndex++;

            if (count($largestConcepType) == 0) {
                break;
            }
        }

        $largestConcepType = $sortedData;

        $concepTypes = $payroll->concept_type;

        $data = [
            $payroll->index,
            $payroll->basic_payroll_staff_data['full_name'],
            $payroll->basic_payroll_staff_data['id_number'],
            $payroll->basic_payroll_staff_data['instruction_degree'],
            $payroll->basic_payroll_staff_data['position'],
            date("d-m-Y", strtotime($payroll->basic_payroll_staff_data['start_date'])),
            $payroll->basic_payroll_staff_data['institution_years'],
        ];

        $total = 0;
        $flagSign = '';

        foreach ($largestConcepType as $key => $conceptType) {
            if (count($conceptType) > 0) {
                $subTotal = 0;

                foreach ($conceptType as $typeKey => $type) {
                    $typeValue = $concepTypes[$key][$typeKey];
                    if (isset($concepTypes[$key]) && isset($concepTypes[$key][$typeKey])) {
                        $typeValue['value'] = str_replace(',', '', $typeValue['value']);

                        if ($typeValue['sign'] != 'NA') {
                            $value = $typeValue['value'] < 0 ? (float)$typeValue['value'] * -1 : (float)$typeValue['value'];
                            array_push($data, $nameDecimalFunction($value, $this->number_decimals->p_value));
                            $flagSign = $typeValue['sign'];
                            if ($typeValue['sign'] == '+') {
                                $subTotal = $subTotal + $nameDecimalFunction($value, $this->number_decimals->p_value);
                            } else {
                                $subTotal = $subTotal - $nameDecimalFunction($value, $this->number_decimals->p_value);
                            }
                        } else {
                            $flagSign = $typeValue['sign'];
                        }
                    } else {
                        if ($type != 'NA') {
                            array_push($data, ' ');
                        } else {
                            $flagSign = $typeValue['sign'];
                        }
                    }
                }

                if ($flagSign != 'NA') {
                    $subTotal = (float)$subTotal;
                    $subTotalValue = $subTotal < 0 ? ($subTotal * -1) : $subTotal;
                    array_push($data, $nameDecimalFunction($subTotalValue, $this->number_decimals->p_value));
                    if ($flagSign == '+') {
                        $total = (float)$total + $subTotalValue;
                    } else {
                        $total = (float)$total - $subTotalValue;
                    }
                }
            }
        }

        array_push($data, $nameDecimalFunction($total, $this->number_decimals->p_value));
        array_push($data, ' ');

        foreach ($largestConcepType as $key => $conceptType) {
            if (count($conceptType) > 0) {
                $totalNA = 0;

                foreach ($conceptType as $typeKey => $type) {
                    if (isset($concepTypes[$key]) && isset($concepTypes[$key][$typeKey])) {
                        $typeValue = $concepTypes[$key][$typeKey];
                        $typeValue['value'] = str_replace(',', '', $typeValue['value']);
                        if ($typeValue['sign'] == 'NA') {
                            $value = $typeValue['value'] < 0 ? (float)$typeValue['value'] * -1 : (float)$typeValue['value'];
                            array_push($data, $nameDecimalFunction($value, $this->number_decimals->p_value));

                            $totalNA = $totalNA + $nameDecimalFunction($value, $this->number_decimals->p_value);
                            $flagSign = $typeValue['sign'];
                        }
                    }
                }

                if ($flagSign == 'NA') {
                    $totalNA = (float)$totalNA;
                    $totalNAValue = $totalNA < 0 ? ($totalNA * -1) : $totalNA;
                    if ($totalNA > 0) {
                        array_push($data, $nameDecimalFunction($totalNAValue, $this->number_decimals->p_value));
                        array_push($data, ' ');
                    }
                }
            }
        }

        foreach ($data as $dataKey => $dataValue) {
            if ($dataKey < 6) {
                $this->total[$dataKey] = ' ';
            } else {
                if (!array_key_exists($dataKey, $this->total)) {
                    $this->total[$dataKey] = $nameDecimalFunction((float)$dataValue, $this->number_decimals->p_value);
                } else {
                    $this->total[$dataKey] = $nameDecimalFunction((float)$this->total[$dataKey] + (float)$dataValue, $this->number_decimals->p_value);
                }
            }
        }

        return $data;
    }

    /**
     * Registra los eventos de exportación
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

        $records = $this->model;
        $countRecords = 0;
        $values = [];
        $payrollName = '';

        foreach ($records as $record) {
            $countRecords += 1;
            $payrollName = $record->payroll->name;

            foreach ($record['concept_type'] as $key => $conceptType) {
                if (!array_key_exists($key, $values)) {
                    $values[$key] = $conceptType;
                } else {
                    $values[$key] += $conceptType;
                }
            }
        }

        $concepTypes = $values;
        $signsOrder = ['+', '-', 'NA'];
        $signIndex = 0;
        $data = [];
        $sortedData = [];

        while (1) {
            foreach ($concepTypes as $key => $concepType) {
                $payrollConceptType = PayrollConceptType::where('name', $key)->first();

                if (isset($payrollConceptType)) {
                    if ($payrollConceptType->sign == $signsOrder[$signIndex]) {
                        $sortedData[$key] = $concepType;
                        unset($concepTypes[$key]);
                    }
                } else {
                    unset($concepTypes[$key]);
                }
            }

            $signIndex++;

            if (count($concepTypes) == 0) {
                break;
            }
        }

        $concepTypes = $sortedData;

        $payroll = $institution->with('logo')->first();
        $payroll_logo = $payroll->logo->file;

        $data = [
            AfterSheet::class => function (AfterSheet $event) use ($payroll_logo, $concepTypes, $countRecords, $institution, $payrollName) {
                $logo = storage_path() . '/pictures/' . $payroll_logo;
                if (file_exists($logo)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('institution_logo');
                    $drawing->setDescription('Logo Institucional');
                    $drawing->setPath(storage_path() . '/pictures/' . $payroll_logo);
                    $drawing->setCoordinates('C1');
                    $drawing->setHeight(45);
                    $drawing->setWorksheet($event->sheet->getDelegate());
                }

                $sheet = $event->sheet;

                $counts = 0;
                $letter = 73;
                $flagSign = '';

                foreach ($concepTypes as $key => $conceptType) {
                    $payrollConceptType = PayrollConceptType::where('name', $key)->first();
                    if ($payrollConceptType->sign != 'NA') {
                        if (count($conceptType) > 0) {
                            if ($counts == 0) {
                                $sheet->mergeCells($this->setCellCharacter($letter) . '5:' . $this->setCellCharacter($letter - 1 + count($conceptType)) . '5');
                                $sheet->setCellValue($this->setCellCharacter($letter) . '5', $key);
                                $sheet->setCellValue($this->setCellCharacter($letter + count($conceptType)) . '5', "Sub-total");
                            } else {
                                $sheet->mergeCells($this->setCellCharacter($letter) . '5:' . $this->setCellCharacter($letter - 1 + count($conceptType)) . '5');
                                $sheet->setCellValue($this->setCellCharacter($letter) . '5', $key);
                                $sheet->setCellValue($this->setCellCharacter($letter + count($conceptType)) . '5', "Sub-total");
                            }
                            $counts = count($conceptType);
                            $letter = $letter + $counts + 1;

                            $flagSign = $payrollConceptType->sign;
                        }
                    }
                }

                if ($flagSign != 'NA' && is_numeric($letter)) {
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

                    $sheet->setCellValue($char . '5', "Total");
                }

                foreach ($concepTypes as $key => $conceptType) {
                    $payrollConceptType = PayrollConceptType::where('name', $key)->first();

                    if ($payrollConceptType->sign == 'NA') {
                        if (count($conceptType) > 0) {
                            $sheet->mergeCells($this->setCellCharacter($letter + 2) . '5:' . $this->setCellCharacter($letter + 1 + count($conceptType)) . '5');
                            $sheet->setCellValue($this->setCellCharacter($letter + 2) . '5', $key);
                            $sheet->setCellValue($this->setCellCharacter($letter + 2 + count($conceptType)) . '5', "Total");

                            $counts = count($conceptType) + 1;
                            $letter = $letter + $counts + 1;

                            $flagSign = $payrollConceptType->sign;
                        }
                    }
                }

                $sheet->setCellValue('B' . $countRecords + 8, "Total");
                $sheet->setCellValue('C' . $countRecords + 8, $countRecords);
                $sheet->setCellValue('D1', $institution->acronym ?? $institution->name);
                $sheet->setCellValue('D2', $payrollName);
                $sheet->setCellValue('D3', date_format(Payroll::find($this->payrollId)->created_at, 'd/m/Y'));

                /* Se colocan los totales de cada tipo de concepto en las celdas correspondientes */

                $dLetter = 72;

                foreach ($this->total as $tKey => $total) {
                    if ($tKey > 5 && is_numeric($dLetter)) {
                        $char = '';
                        $count = 65;
                        $countWhile = -1;
                        $newLetter = $dLetter;
                        while ($newLetter > 90) {
                            $dLetterValue = intdiv($newLetter - $count, 26);
                            $char = $char == '' ? chr($count) : chr($count + $dLetterValue + $countWhile);
                            $newLetter = $newLetter - 26;
                            $countWhile += 1;
                        }

                        if ($char == '') {
                            $char = chr($newLetter);
                        } else {
                            $char = $char . chr($newLetter);
                        }

                        if ($total != '0.0') {
                            $sheet->setCellValue($char . $countRecords + 8, currency_format($total, $this->number_decimals->p_value));
                        } else {
                            $sheet->setCellValue($char . $countRecords + 8, ' ');
                        }

                        $dLetter = $dLetter + 1;
                    }
                }

                $cellRange = 'D5:' . $char . '5'; // All headers

                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
            },
        ];

        return $data;
    }

    /**
     * Establece la celda donde va a ser colocada la información
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param     integer|string $letter Número según el código ascii para el cáracter
     *
     * @return    string                 Carácter de la celda en la que se va a ubicar la información
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
