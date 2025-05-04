<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * @class ProfessionalStaffExport
 * @brief Clase para exportar la hoja de validaciones de datos profesionales
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProfessionalStaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    /**
     * Errores encontrados en la exportación de datos
     *
     * @var array $errors
     */
    protected $errors;

    /**
     * Método constructor de la clase
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

    /**
     * Título de la hoja a exportar
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Profesionales';
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
