<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * @class StaffExport
 * @brief Clase para exportar la hoja de validaciones de datos de personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class StaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    /**
     * Listado de errores
     *
     * @var array $errors
     */
    protected $errors;

    /**
     * Metodo constructor de la clase
     *
     * @param array $errors Listado de errores
     *
     * @return void
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Encabezados de las columnas de la hoja a exportar
     *
     * @return array
     */
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

    /**
     * Título de la hoja a exportar
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Personales';
    }

    /**
     * Retorna los errores de la hoja
     *
     * @return array
     */
    public function array(): array
    {
        return $this->errors;
    }
}
