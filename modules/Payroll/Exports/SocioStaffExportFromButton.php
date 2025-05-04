<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class SocioStaffExportFromButton extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents,
    WithCustomStartCell
{
    use RegistersEventListeners;

    /**
     * Método constructor de la clase
     *
     * @param mixed $collection
     *
     * @return void
     */
    public function __construct(protected mixed $collection = null)
    {
        //
    }

    /**
     * Metodo para obtener la colección de datos
     *
     * @return mixed
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Cédula del Trabajador',
            'Estado civil del trabajador',
            'Parentesco',
            'Nombres del pariente',
            'Apellidos del pariente',
            'Cédula del pariente',
            'Fecha de nacimiento',
            'Dirección',
            'Genero',
            'Es estudiante?',
            'Nivel de escolaridad',
            'centro de estudio',
            'Posee una beca?',
            'Tipo de beca',
            'Posee una discapacidad?',
            'Tipo de discapacidad',
        ];
    }

    /**
     * Mapeo de los datos de la hoja
     *
     * @param mixed $data Datos de la hoja
     *
     * @return array
     */
    public function map($data): array
    {
        $map = [
            $data['payroll_staff_id_number'],
            $data['marital_status'],
            $data['payroll_relationship'] ??  '',
            $data['first_name'] ??  '',
            $data['last_name'] ??  '',
            $data['id_number'] ??  '',
            empty($data['birthdate'] ?? '') ? '' : Carbon::createFromFormat('Y-m-d', $data['birthdate'])->format('d-m-Y'),
            $data['address'] ??  '',
            $data['gender'] ??  '',
            $data['student'] === null ? '' : (!empty($data['student']) ? 'Si' : 'No'),
            $data['student'] === null
                ? ''
                : (!empty($data['student']) ? $data['student']['payroll_schooling_level'] : ''),
            $data['student'] === null ? '' : (!empty($data['student']) ? $data['student']['study_center'] : ''),
            $data['student'] === null ? '' : (!empty($data['student']['scholarships']) ? 'Si' : 'No'),
            $data['student'] === null
                ? ''
                : (!empty($data['student']['scholarships'])
                    ? $data['student']['scholarships']['scholarship_type']
                    : ''
                ),
            $data['disability'] === null ? '' : (!empty($data['disability']) ? 'Si' : 'No'),
            $data['disability'] === null ? '' : (!empty($data['disability']) ? $data['disability'] : ''),

        ];
        return $map;
    }

    /**
     * Título de la hoja a exportar
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Socioeconomicos';
    }

    /**
     * Celda en la que se debe comenzar a escribir el archivo a exportar
     *
     * @return string
     */
    public function startCell(): string
    {
        return 'A1';
    }

    /**
     * Registra los eventos de la clase
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                $sheet = $event->sheet->getDelegate();
                /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                $marital_status = 'validation!$A$2:$A$5000';
                $payroll_schooling_level = 'validation!$B$2:$B$5000';
                $disability = 'validation!$C$2:$C$5000';
                $bolean = 'validation!$D$2:$D$5000';
                $scholarships = 'validation!$E$2:$E$5000';
                $payroll_relationship = 'validation!$F$2:$F$5000';
                $gender = 'validation!$G$2:$G$5000';

                $this->setFunctionList($sheet, 'B', 2, null, 'maritalStatus', $marital_status);
                $this->setFunctionList($sheet, 'C', 2, null, 'relationship', $payroll_relationship);
                $this->setFunctionList($sheet, 'I', 2, null, 'gender', $gender);
                $this->setFunctionList($sheet, 'J', 2, null, 'bolean', $bolean);
                $this->setFunctionList($sheet, 'K', 2, null, 'schoolingLevel', $payroll_schooling_level);
                $this->setFunctionList($sheet, 'M', 2, null, 'bolean', $bolean);
                $this->setFunctionList($sheet, 'N', 2, null, 'scholarship', $scholarships);
                $this->setFunctionList($sheet, 'O', 2, null, 'bolean', $bolean);
                $this->setFunctionList($sheet, 'P', 2, null, 'disability', $disability);
            },
        ];
    }
}
