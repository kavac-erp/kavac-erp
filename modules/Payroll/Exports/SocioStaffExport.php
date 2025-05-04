<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SocioStaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
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
            'estado_civil',
            'nombres_del_pariente1',
            'apellidos_del_pariente1',
            'cedula_de_identidad_del_pariente1',
            'fecha_de_nacimiento_del_pariente1',
            'es_estudiante1',
            'nivel_de_escolaridad1',
            'centro_de_estudio1',
            'posee_una_discapacidad1',
            'discapacidad1',
            'edad1',
            'direccion1',
            'posee_una_beca1',
            'tipo_de_beca1',
            'genero1',
            'parentesco1',
            'nombres_del_pariente2',
            'apellidos_del_pariente2',
            'cedula_de_identidad_del_pariente2',
            'fecha_de_nacimiento_del_pariente2',
            'es_estudiante2',
            'nivel_de_escolaridad2',
            'centro_de_estudio2',
            'posee_una_discapacidad2',
            'discapacidad2',
            'edad2',
            'direccion2',
            'posee_una_beca2',
            'tipo_de_beca2',
            'genero2',
            'parentesco2',
            'nombres_del_pariente3',
            'apellidos_del_pariente3',
            'cedula_de_identidad_del_pariente3',
            'fecha_de_nacimiento_del_pariente3',
            'es_estudiante3',
            'nivel_de_escolaridad3',
            'centro_de_estudio3',
            'posee_una_discapacidad3',
            'discapacidad3',
            'edad3',
            'direccion3',
            'posee_una_beca3',
            'tipo_de_beca3',
            'genero3',
            'parentesco3',
        ];
    }

    public function title(): string
    {
        return 'Datos Socioeconomicos';
    }

    public function array(): array
    {
        return $this->errors;
    }
}
