<?php

namespace Modules\Payroll\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Payroll\Models\PayrollClassSchedule;
use Modules\Payroll\Models\PayrollLanguage;
use Modules\Payroll\Models\PayrollLanguageLevel;
use Modules\Payroll\Models\PayrollProfessional;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStudy;
use Modules\Payroll\Models\PayrollCourse;
use Modules\Payroll\Models\PayrollAcknowledgment;
use Modules\Payroll\Models\PayrollStudyType;
use Modules\Payroll\Models\PayrollInstructionDegree;
use Modules\Payroll\Models\Profession;
use Maatwebsite\Excel\Validators\Failure;

/**
 * @class ProfessionalStaffImport
 * @brief Importa un archivo de datos profesionales del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProfessionalStaffImport implements
    ToModel,
    WithValidation,
    WithHeadingRow,
    SkipsOnFailure,
    SkipsEmptyRows
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    /**
     * Método constructor de la clase
     *
     * @param string $fileErrosPath Ruta donde se guardan los archivos de errores
     *
     * @return void
     */
    public function __construct(
        protected string $fileErrosPath,
    ) {
    }

    /**
     * Modelo para importar datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        /* Datos del tipo de bien al cual asociar la información del bien */
        DB::transaction(function () use ($row) {
            $payrollProfessional = PayrollProfessional::updateOrCreate(
                [
                    'payroll_staff_id' => $row['id'],
                ],
                [
                    'payroll_staff_id' => $row['id'],
                    'payroll_instruction_degree_id' => $row['grado_de_instruccion'],
                    'is_student' => $row['es_estudiante'],
                    'payroll_study_type_id' => ($row['es_estudiante']) ? $row['tipo_de_estudio'] : null,
                    'study_program_name' => ($row['es_estudiante']) ? $row['nombre_del_programa_de_estudio'] : null,
                ]
            );
            for ($iter = 1; $iter <= 2; $iter++) {
                $university_name = (isset($row['nombre_de_la_universidad' . $iter]) && !is_null($row['nombre_de_la_universidad' . $iter]) && trim($row['nombre_de_la_universidad' . $iter]) != '');
                $ano_de_graduacion = (isset($row['ano_de_graduacion' . $iter]) && !is_null($row['ano_de_graduacion' . $iter]) && trim($row['ano_de_graduacion' . $iter]) != '');
                $tipo_de_estudio = (isset($row['tipo_de_estudio' . $iter]) && !is_null($row['tipo_de_estudio' . $iter]) && is_int($row['tipo_de_estudio' . $iter]) && $row['tipo_de_estudio' . $iter] > 0);
                $profesion = (isset($row['profesion' . $iter]) && !is_null($row['profesion' . $iter]) && is_int($row['profesion' . $iter]) && $row['profesion' . $iter] > 0);
                if ($university_name && $ano_de_graduacion && $tipo_de_estudio && $profesion) {
                    PayrollStudy::firstOrCreate(
                        [
                            'payroll_professional_id' => $payrollProfessional->id,
                            'university_name' => $row['nombre_de_la_universidad' . $iter],
                            'graduation_year' => $row['ano_de_graduacion' . $iter],
                            'payroll_study_type_id' => $row['tipo_de_estudio' . $iter],
                            'profession_id' => $row['profesion' . $iter],
                        ]
                    );
                }
            }

            for ($iter = 1; $iter <= 2; $iter++) {
                $idioma = (isset($row['idioma' . $iter]) && !is_null($row['idioma' . $iter]) && is_int($row['idioma' . $iter]) && $row['idioma' . $iter] > 0);
                $nivel_de_idioma = (isset($row['nivel_de_idioma' . $iter]) && !is_null($row['nivel_de_idioma' . $iter]) && is_int($row['nivel_de_idioma' . $iter]) && $row['nivel_de_idioma' . $iter] > 0);
                if ($idioma && $nivel_de_idioma) {
                    $sameLenguage = DB::table('payroll_lang_prof')->upsert(
                        [
                            'payroll_lang_id' => $row['idioma' . $iter],
                            'payroll_prof_id' => $payrollProfessional->id,
                            'payroll_language_level_id' => $row['nivel_de_idioma' . $iter],
                        ],
                        [
                            'payroll_lang_id', 'payroll_prof_id',
                        ]
                    );
                }
            }

            PayrollClassSchedule::firstOrCreate([
                'payroll_professional_id' => $payrollProfessional->id,
            ]);
            PayrollCourse::firstOrCreate([
                'payroll_professional_id' => $payrollProfessional->id
            ]);
            PayrollAcknowledgment::firstOrCreate([
                'payroll_professional_id' => $payrollProfessional->id
            ]);
        });
    }

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo con los datos
     * @param integer $index Indice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {

        if (isset($data["cedula_de_identidad"]) && !is_null($data["cedula_de_identidad"]) && $data["cedula_de_identidad"] != "" and $data["cedula_de_identidad"] != "null" and $data["cedula_de_identidad"] != null) {
            $usuario = PayrollStaff::where(['id_number' => $data["cedula_de_identidad"],])->first();
            $data["id"] = $usuario?->id;
            if (empty($data["id"])) {
                $data["cedula_de_identidad_value"] = $data["cedula_de_identidad"];
            }
        }
        $data["es_estudiante"] = (isset($data["es_estudiante"]) && !is_null($data["es_estudiante"]) && (strtoupper($data["es_estudiante"] == "True") || strtoupper($data["es_estudiante"]) == "SI"));

        for ($iter = 0; $iter <= 2; $iter++) {
            $key = $iter > 0 ? $iter : '';
            if (isset($data['tipo_de_estudio' . $key]) && !is_null($data['tipo_de_estudio' . $key]) && $data['tipo_de_estudio' . $key] && trim($data['tipo_de_estudio' . $key]) != '') {
                $modelID = false;
                $data['tipo_de_estudio' . $key . '_value'] = $data['tipo_de_estudio' . $key];
                if (is_int($data['tipo_de_estudio' . $key]) && $data['tipo_de_estudio' . $key] > 0) {
                    $modelID = PayrollStudyType::where(['id' => $data['tipo_de_estudio' . $key],])->first();
                }
                if (!$modelID && trim($data['tipo_de_estudio' . $key]) != '') {
                    $modelID = PayrollStudyType::where(['name' => $data['tipo_de_estudio' . $key],])->first();
                }
                if ($modelID) {
                    $data['tipo_de_estudio' . $key] = $modelID->id;
                    $data['tipo_de_estudio' . $key . '_value'] = false;
                }
            }
        }
        if (isset($data['grado_de_instruccion']) && $data['grado_de_instruccion'] && !is_null($data['grado_de_instruccion']) && trim($data['grado_de_instruccion']) != '') {
            $modelID = false;
            $data['grado_de_instruccion_value'] = $data['grado_de_instruccion'];
            if (is_int($data['grado_de_instruccion']) && $data['grado_de_instruccion'] > 0) {
                $modelID = PayrollInstructionDegree::where(['id' => $data['grado_de_instruccion'],])->first();
            }
            if (!$modelID && trim($data['grado_de_instruccion']) != '') {
                $modelID = PayrollInstructionDegree::where(['name' => $data['grado_de_instruccion'],])->first();
            }
            if ($modelID) {
                $data['grado_de_instruccion'] = $modelID->id;
                $data['grado_de_instruccion_value'] = false;
            }
        }

        for ($iter = 1; $iter <= 2; $iter++) {
            if (isset($data['profesion' . $iter]) && $data['profesion' . $iter] && !is_null($data['profesion' . $iter]) && trim($data['profesion' . $iter]) != '') {
                $modelID = false;
                $data['profesion' . $iter . '_value'] = $data['profesion' . $iter];
                if (is_int($data['profesion' . $iter]) && $data['profesion' . $iter] > 0) {
                    $modelID = Profession::where(['id' => $data['profesion' . $iter],])->first();
                }
                if (!$modelID && trim($data['profesion' . $iter]) != '') {
                    $modelID = Profession::where(['name' => $data['profesion' . $iter],])->first();
                }
                if ($modelID) {
                    $data['profesion' . $iter] = $modelID->id;
                    $data['profesion' . $iter . '_value'] = false;
                }
            }
        }

        for ($iter = 1; $iter <= 2; $iter++) {
            if (isset($data['idioma' . $iter]) && $data['idioma' . $iter] && !is_null($data['idioma' . $iter]) && trim($data['idioma' . $iter]) != '') {
                $modelID = false;
                $data['idioma' . $iter . '_value'] = $data['idioma' . $iter];
                if (is_int($data['idioma' . $iter]) && $data['idioma' . $iter] > 0) {
                    $modelID = PayrollLanguage::where(['id' => $data['idioma' . $iter],])->first();
                }
                if (!$modelID && trim($data['idioma' . $iter]) != '') {
                    $modelID = PayrollLanguage::where(['name' => $data['idioma' . $iter],])->first();
                }
                if ($modelID) {
                    $data['idioma' . $iter] = $modelID->id;
                    $data['idioma' . $iter . '_value'] = false;
                }
            }
        }

        for ($iter = 1; $iter <= 2; $iter++) {
            if (isset($data['nivel_de_idioma' . $iter]) && $data['nivel_de_idioma' . $iter] && !is_null($data['nivel_de_idioma' . $iter]) && trim($data['nivel_de_idioma' . $iter]) != '') {
                $modelID = false;
                $data['nivel_de_idioma' . $iter . '_value'] = $data['nivel_de_idioma' . $iter];
                if (is_int($data['nivel_de_idioma' . $iter]) && $data['nivel_de_idioma' . $iter] > 0) {
                    $modelID = PayrollLanguageLevel::where(['id' => $data['nivel_de_idioma' . $iter],])->first();
                }
                if (!$modelID && trim($data['nivel_de_idioma' . $iter]) != '') {
                    $modelID = PayrollLanguageLevel::where(['name' => $data['nivel_de_idioma' . $iter],])->first();
                }
                if ($modelID) {
                    $data['nivel_de_idioma' . $iter] = $modelID->id;
                    $data['nivel_de_idioma' . $iter . '_value'] = false;
                }
            }
        }
        return $data;
    }

    /**
     * Callback de error de validación
     *
     * @param object $failures Arreglo columnas que fallaron en la validación
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $validationErrors = [
                'row' => $failure->row(),
                'attribute' => str_replace('_value', '', $failure->attribute()),
                'error' => $failure->errors()[0],
                'sheetName' => 'Datos Profesionales'
            ];
            $jsonErrors = json_encode($validationErrors);
            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->fileErrosPath, $jsonErrors);
        }
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'cedula_de_identidad' => ['required'],
            'cedula_de_identidad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'La cédula de identidad (' . strip_tags($value) .
                        ') no coincide con los registros en la base de datos del sistema'
                    );
                }
            },
            'grado_de_instruccion' => ['required'],
            'es_estudiante' => ['required'],
            'tipo_de_estudio' => ['required_if:es_estudiante,TRUE'],
            'nombre_del_programa_de_estudio' => ['required_if:es_estudiante,TRUE'],
            'nombre_de_la_universidad1' => ['required_with:*.ano_de_graduacion1,*.tipo_de_estudio1,*.profesion1'],
            'ano_de_graduacion1' => ['required_with:*.nombre_de_la_universidad1,*.tipo_de_estudio1,*.profesion1'],
            'tipo_de_estudio1' => ['required_with:*.nombre_de_la_universidad1,*.ano_de_graduacion1,*.profesion1'],
            'profesion1' => ['required_with:*.nombre_de_la_universidad1,*.ano_de_graduacion1,*.tipo_de_estudio1'],
            'nombre_de_la_universidad2' => ['required_with:*.ano_de_graduacion2,tipo_de_estudio2,profesion2'],
            'ano_de_graduacion2' => ['required_with:*.nombre_de_la_universidad2,tipo_de_estudio2,profesion2'],
            'tipo_de_estudio2' => ['required_with:*.nombre_de_la_universidad2,ano_de_graduacion2,profesion2'],
            'profesion2' => ['required_with:*.nombre_de_la_universidad2,ano_de_graduacion2,tipo_de_estudio2'],
            'idioma1' => ['required_with:*.nivel_de_idioma1'],
            'nivel_de_idioma1' => ['required_with:*.idioma1'],
            'idioma2' => ['required_with:*.nivel_de_idioma2'],
            'nivel_de_idioma2' => ['required_with:*.idioma2'],
            'tipo_de_estudio_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Estudio ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_estudio1_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Estudio ingresado en la sección número 1 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_estudio2_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Estudio ingresado en la sección número 2 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'profesion1_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre de la Profesión ingresado en la sección número 1 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'profesion2_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre de la Profesión ingresado en la sección número 2 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'idioma1_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Idioma ingresado en la sección número 1 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'nivel_de_idioma1_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Nivel de Idioma ingresado en la sección número 1 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'idioma2_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Idioma ingresado en la sección número 2 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'nivel_de_idioma2_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Nivel de Idioma ingresado en la sección número 2 de Estudios anteriores (' .
                        strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
        ];
    }
}
