<?php

namespace Modules\Payroll\Exports;

use App\Exports\DataExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Models\PayrollLanguage;
use Modules\Payroll\Models\PayrollLanguageLevel;
use Modules\Payroll\Models\PayrollProfessional;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStudy;
use Modules\Payroll\Models\PayrollInstructionDegree;
use Modules\Payroll\Models\PayrollStudyType;
use Modules\Payroll\Models\Profession;

/**
 * @class ProfessionalStaffExportFromButton
 * @brief Clase que exporta el listado de personal de la nómina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProfessionalStaffExportFromButton extends DataExport implements
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
     * Mapea los datos de la hoja
     *
     * @param array|object $data Datos de la hoja
     *
     * @return array
     */
    public function map($data): array
    {
        $staff = PayrollStaff::find($data['payroll_staff_id']);
        $professional = PayrollProfessional::where(['payroll_staff_id' => $staff->id])->first();
        $studyType = PayrollStudyType::find($data['payroll_study_type_id']);
        $payrollInstructionDegree = PayrollInstructionDegree::find($data['payroll_instruction_degree_id']);

        $map = [
            $data['cedula'] = $staff->id_number,
            $payrollInstructionDegree->name ?? '',
            $data['is_student'] ? 'Si' : 'No',
            $studyType->name ?? '',
            $data['study_program_name'],
        ];
        if ($professional) {
            $study = PayrollStudy::where(['payroll_professional_id' => $professional->id])->get();

            if ($study) {
                for ($i = 0; $i < 2; $i++) {
                    if ($i == 2) {
                        break;
                    }
                    $map[] = $study[$i]->university_name ?? '';
                    $map[] = $study[$i]->graduation_year ?? '';
                    $map[] = PayrollStudyType::find($study[$i]->payroll_study_type_id ?? null)?->name ?? '';
                    $map[] = Profession::find($study[$i]->profession_id ?? null)?->name ?? '';
                }
            } else {
                for ($i = 0; $i < 2; $i++) {
                    if ($i == 2) {
                        break;
                    }
                    $map[] = '';
                    $map[] = '';
                    $map[] = '';
                    $map[] = '';
                }
            }
            $idioma = DB::table('payroll_lang_prof')
                ->where('payroll_prof_id', '=', $professional->id)
                ->get()->toArray();
            $idiomaCount = DB::table('payroll_lang_prof')
                ->where('payroll_prof_id', '=', $professional->id)
                ->count();
            if ($idioma) {
                for ($i = 0; $i < 2; $i++) {
                    if ($i == 2) {
                        break;
                    }
                    //revisar si existe la key
                    if (array_key_exists($i, $idioma)) {
                        $map[] = PayrollLanguage::find($idioma[$i]->payroll_lang_id ?? null)?->name ?? '';
                        $map[] = PayrollLanguageLevel::find($idioma[$i]->payroll_language_level_id)?->name ?? '';
                    } else {
                        $map[] = '';
                        $map[] = '';
                    }
                }
            }
        }
        return $map;
    }

    /**
     * Titulo de la hoja
     *
     * @return string
     */
    public function title(): string
    {
        return 'Datos Profesionales';
    }

    /**
     * Registra los eventos de la hoja
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

                $this->setFunctionList($sheet, 'B', 2, null, 'validateA', $validationRangeA);
                $this->setFunctionList($sheet, 'C', 2, null, 'validateF', $validationRangeF);
                $this->setFunctionList($sheet, 'D', 2, null, 'validateB', $validationRangeB);
                $this->setFunctionList($sheet, 'H', 2, null, 'validateB', $validationRangeB);
                $this->setFunctionList($sheet, 'I', 2, null, 'validateC', $validationRangeC);
                $this->setFunctionList($sheet, 'L', 2, null, 'validateB', $validationRangeB);
                $this->setFunctionList($sheet, 'M', 2, null, 'validateC', $validationRangeC);
                $this->setFunctionList($sheet, 'N', 2, null, 'validateD', $validationRangeD);
                $this->setFunctionList($sheet, 'O', 2, null, 'validateE', $validationRangeE);
                $this->setFunctionList($sheet, 'P', 2, null, 'validateD', $validationRangeD);
                $this->setFunctionList($sheet, 'Q', 2, null, 'validateE', $validationRangeE);
            },
        ];
    }
}
