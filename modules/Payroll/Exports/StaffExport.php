<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function headings(): array
    {
        return [
            'nombres',
            'apellidos',
            'code',
            'nacionalidad',
            'cedula_de_identidad',
            'pasaporte',
            'correo_electronico',
            'fecha_de_nacimiento',
            'genero',
            'nombre_y_apellido_de_persona_de_contacto',
            'telefono_de_persona_de_contacto',
            'posee_una_discapacidad',
            'discapacidad',
            'tipo_de_sangre',
            'seguro_social',
            'posee_licencia_de_conducir',
            'grado_de_licencia',
            'parroquia',
            'direccion',
            'historial_medico',
            'pieza_de_uniforme_1',
            'talla_1',
            'pieza_de_uniforme_2',
            'talla_2',
            'pieza_de_uniforme_3',
            'talla_3',
        ];
    }
    // public function map($errors): array
    // {
    //     return [
    //         $errors->row,
    //         $errors->attribute,
    //         $errors->errors[0],

    //     ];
    // }

    public function title(): string
    {
        return 'Datos Personales';
    }

    public function array(): array
    {
        return $this->errors;
    }
}
