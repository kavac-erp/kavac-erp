<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use App\Models\Parish;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollBloodType;
use Modules\Payroll\Models\PayrollDisability;
use Modules\Payroll\Models\PayrollGender;
use Modules\Payroll\Models\PayrollLicenseDegree;
use Modules\Payroll\Models\PayrollNationality;

/**
 * @class StaffExportFromButton
 * @brief Clase que exporta el listado de registros del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class StaffExportFromButton extends DataExport implements
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents
{
    use RegistersEventListeners;

    /**
     * Encabezados de la hoja
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
            'rif',
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
        ];
    }

    /**
     * Mapeo de los datos de la hoja a exportar
     *
     * @param array $data Registros de la hoja
     *
     * @return array
     */
    public function map($data): array
    {
        $nation = PayrollNationality::find($data['payroll_nationality_id']);
        $gender = PayrollGender::find($data['payroll_gender_id']);
        $bloodType = PayrollBloodType::find($data['payroll_blood_type_id']);
        $disability = PayrollDisability::find($data['payroll_disability_id']);
        $license = PayrollLicenseDegree::find($data['payroll_license_degree_id']);
        $parish = Parish::find($data['parish_id']);

        return [
            $data["first_name"],
            $data['last_name'],
            $data['code'],
            $nation?->name ?? '',
            $data['id_number'],
            $data['rif'] ?? '',
            $data['passport'],
            $data['email'],
            Carbon::createFromFormat('Y-m-d', $data['birthdate'])->format('d-m-Y'),
            $gender?->name ?? '',
            $data['emergency_contact'],
            $data['emergency_phone'],
            $data['has_disability'] ? 'Si' : 'No',
            $disability?->name ?? '',
            $bloodType?->name ?? '',
            $data['social_security'],
            $data['has_driver_license'] ? 'Si' : 'No',
            $license?->name ?? '',
            $parish?->name ?? '',
            $data['address'],
            strip_tags($data['medical_history']),
        ];
    }

    /**
     * Obtiene el titulo de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Personales';
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
                $validationRangeA = 'validation!$A$2:$A$5000';
                $validationRangeB = 'validation!$B$2:$B$5000';
                $validationRangeC = 'validation!$C$2:$C$5000';
                $validationRangeD = 'validation!$D$2:$D$5000';
                $validationRangeE = 'validation!$E$2:$E$5000';
                $validationRangeF = 'validation!$F$2:$F$5000';
                $validationRangeG = 'validation!$G$2:$G$5000';

                $this->setFunctionList($sheet, 'D', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'J', 2, null, 'validateB', $validationRangeB);
                $this->setFunctionList($sheet, 'N', 2, null, 'validateC', $validationRangeC);
                $this->setFunctionList($sheet, 'O', 2, null, 'validateD', $validationRangeD);
                $this->setFunctionList($sheet, 'R', 2, null, 'validateE', $validationRangeE);
                $this->setFunctionList($sheet, 'S', 2, null, 'validateF', $validationRangeF);
                $this->setFunctionList($sheet, 'M', 2, null, 'validateG', $validationRangeG);
                $this->setFunctionList($sheet, 'Q', 2, null, 'validateG', $validationRangeG);
            },
        ];
    }
}
