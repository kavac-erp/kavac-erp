<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProfessionalStaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function headings(): array
    {
        return [
            'cedula_de_identidad',
            'grado_de_instruccion',
            'es_estudiante',
            'tipo_de_estudio',
            'nombre_del_programa_de_estudio',
            'nombre_de_la_universidad1',
            'ano_de_graduacion1',
            'tipo_de_estudio1',
            'profesion1',
            'nombre_de_la_universidad2',
            'ano_de_graduacion2',
            'tipo_de_estudio2',
            'profesion2',
            'idioma1',
            'nivel_de_idioma1',
            'idioma2',
            'nivel_de_idioma2',
        ];
    }

    public function title(): string
    {
        return 'Datos Profesionales';
    }

    public function array(): array
    {

        return $this->errors;
    }
}
