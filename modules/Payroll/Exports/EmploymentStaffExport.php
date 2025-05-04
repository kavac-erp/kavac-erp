<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * @class EmploymentStaffExport
 * @brief Clase que exporta la información del personal
 *
 * @author Ing. Francisco Escala <fescala@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class EmploymentStaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    /**
     * Lista de errores encontrados en la exportación de datos
     *
     * @var array $errors
     */
    protected $errors;

    /**
     * Método constructor de la clase.
     *
     * @param array $errors Lista de errores encontrados en la exportación de datos
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
            'esta_activo',
            'tipo_de_inactividad',
            'fecha_de_egreso_de_la_institucion',
            'fecha_de_ingreso_a_la_institucion',
            'correo_institucional',
            'tipo_de_cargo',
            'cargo',
            'tipo_de_personal',
            'tipo_de_contrato',
            'departamento',
            'descripcion_de_funciones',
            'nombre_de_la_organizacion_anterior_1',
            'telefono_de_la_organizacion1',
            'tipo_de_sector1',
            'cargo1',
            'tipo_de_personal1',
            'fecha_de_inicio1',
            'fecha_de_cese1',
            'nombre_de_la_organizacion_anterior2',
            'telefono_de_la_organizacion2',
            'tipo_de_sector2',
            'cargo2',
            'tipo_de_personal2',
            'fecha_de_inicio2',
            'fecha_de_cese2',
            'nombre_de_la_organizacion_anterior3',
            'telefono_de_la_organizacion3',
            'tipo_de_sector3',
            'cargo3',
            'tipo_de_personal3',
            'fecha_de_inicio3',
            'fecha_de_cese3',
            'institution',
            'operation_date',
            'tipo_de_cargo_value',
            'cargo_value',
            'cargo1_value',
            'tipo_de_personal_value',
            'tipo_de_personal1_value',
            'tipo_de_sector1_value',
            'tipo_de_contrato_value',
            'departamento_value',
            'ficha_expediente'
        ];
    }

    /**
     * Establece el tiúlo de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Laborales';
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
