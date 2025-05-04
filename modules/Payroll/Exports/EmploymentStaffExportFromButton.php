<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollSectorType;
use Modules\Payroll\Models\PayrollStaffType;

/**
 * @class EmploymentStaffExport
 * @brief Clase que exporta el listado de personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class EmploymentStaffExportFromButton extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents
{
    use RegistersEventListeners;

    /**
     * Encabezados de las columnas
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
            'coordinacion',
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
            'ficha_expediente',
        ];
    }

    /**
     * Mapeo de los datos
     *
     * @param object $data Datos de la hoja
     *
     * @return array
     */
    public function map($data): array
    {
        $staff = $data->payrollStaff;
        $map = [
            $data['cedula'] = $staff?->id_number ?? '',
        ];
        if ($data['active'] == true) {
                $map[] = 'Si';
                $map[] = '';
        } else {
            $map[] = 'No';
            $map[] = $data->payrollInactivityType?->name ?? '';
        }
        if ($data['end_date']) {
            $map[] = Carbon::createFromFormat('Y-m-d', $data['end_date'])->format('d-m-Y');
        } else {
            $map[] = '';
        }
            $map[] = Carbon::createFromFormat('Y-m-d', $data['start_date'])->format('d-m-Y')  ?? '';
            $map[] = $data['institution_email']  ?? '';
            $map[] = $data->payrollPositionType?->name  ?? '';
            $map[] = $data->payrollPosition?->name  ?? '';
            $map[] = $data->payrollCoordination?->name  ?? '';
            $map[] = $data->payrollStaffType?->name  ?? '';
            $map[] = $data->payrollContractType?->name  ?? '';
            $map[] = $data->department?->name  ?? '';
            $map[] = strip_tags($data['function_description']  ?? '');

        $data['payroll_previous_job'] = $data->payrollPreviousJob->toArray() ?? [];
        for ($i = 0; $i < 3; $i++) {
            if ($i == 3) {
                break;
            }

            if (array_key_exists($i, $data['payroll_previous_job'])) {
                $map[] = $data['payroll_previous_job'][$i]['organization_name'];
                $map[] = $data['payroll_previous_job'][$i]['organization_phone'] ?? '';
                $map[] = PayrollSectorType::find($data['payroll_previous_job'][$i]['payroll_sector_type_id'] ?? null)?->name ?? '';
                $map[] = $data['payroll_previous_job'][$i]['previous_position'] ?? '';
                $map[] = PayrollStaffType::find($data['payroll_previous_job'][$i]['payroll_staff_type_id'] ?? null)?->name ?? '';
                $map[] = Carbon::createFromFormat('Y-m-d', $data['payroll_previous_job'][$i]['start_date'])->format('d-m-Y') ?? '';
                $map[] = Carbon::createFromFormat('Y-m-d', $data['payroll_previous_job'][$i]['end_date'])->format('d-m-Y') ?? '';
            } else {
                $map[] = '';
                $map[] = '';
                $map[] = '';
                $map[] = '';
                $map[] = '';
                $map[] = '';
                $map[] = '';
            }
        }
        $map[] = $data['worksheet_code'];
        return $map;
    }

    /**
     * Título de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Laborales';
    }

    /**
     * Registra eventos de la hoja

     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /* Se crea una instancia Worksheet para acceder a las dos sheet. */
                $sheet = $event->sheet->getDelegate();
                /* Se establece el valor del rango para instanciarlo en la formula. (NombreSheet!Rango) */
                $validationRangeA = 'validation!$A$2:$A$5000';
                $validationRangeB = 'validation!$B$2:$B$5000';
                $validationRangeC = 'validation!$C$2:$C$5000';
                $validationRangeD = 'validation!$D$2:$D$5000';
                $validationRangeE = 'validation!$E$2:$E$5000';
                $validationRangeF = 'validation!$F$2:$F$5000';
                $validationRangeG = 'validation!$G$2:$G$5000';
                $validationRangeH = 'validation!$H$2:$H$5000';
                $validationRangeI = 'validation!$I$2:$I$5000';

                $this->setFunctionList($sheet, 'B', 2, null, 'validateH', $validationRangeH);
                $this->setFunctionList($sheet, 'C', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'G', 2, null, 'validateB', $validationRangeB);
                $this->setFunctionList($sheet, 'H', 2, null, 'validateC', $validationRangeC);
                $this->setFunctionList($sheet, 'I', 2, null, 'validateI', $validationRangeI);
                $this->setFunctionList($sheet, 'J', 2, null, 'validateD', $validationRangeD);
                $this->setFunctionList($sheet, 'K', 2, null, 'validateE', $validationRangeE);
                $this->setFunctionList($sheet, 'L', 2, null, 'validateF', $validationRangeF);
                $this->setFunctionList($sheet, 'P', 2, null, 'validateG', $validationRangeG);
                $this->setFunctionList($sheet, 'R', 2, null, 'validateD', $validationRangeD);
                $this->setFunctionList($sheet, 'W', 2, null, 'validateG', $validationRangeG);
                $this->setFunctionList($sheet, 'Y', 2, null, 'validateD', $validationRangeD);
                $this->setFunctionList($sheet, 'AD', 2, null, 'validateG', $validationRangeG);
                $this->setFunctionList($sheet, 'AF', 2, null, 'validateD', $validationRangeD);
            },
        ];
    }
}
