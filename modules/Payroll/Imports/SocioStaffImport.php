<?php

namespace Modules\Payroll\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Payroll\Models\MaritalStatus;
use Modules\Payroll\Models\PayrollDisability;
use Modules\Payroll\Models\PayrollSchoolingLevel;
use Modules\Payroll\Models\PayrollFamilyBurden;
use Modules\Payroll\Models\PayrollSocioeconomic;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollScholarshipType;
use Modules\Payroll\Models\PayrollRelationship;
use App\Models\Gender;
use Carbon\Carbon;

/**
 * @class SocioStaffImport
 * @brief Importa un archivo de datos socioeconómicos del personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SocioStaffImport implements
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
        //
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
        $data = [
            'first_name' => $row['nombres_del_pariente'],
            'last_name' => $row['apellidos_del_pariente'],
            'id_number' => $row['cedula_del_pariente'],
            'birthdate' => !empty($row['fecha_de_nacimiento']) ? Carbon::createFromFormat('d-m-Y', $row['fecha_de_nacimiento'])->format('Y-m-d') : null,
            'age' => Carbon::parse($row['fecha_de_nacimiento'])->age,
            'address' => $row['direccion'],
            'payroll_gender_id' => $row['genero'],
            'payroll_relationships_id' => $row['parentesco'],
            'payroll_schooling_level_id' => $row['nivel_de_escolaridad'],
            'payroll_scholarship_types_id' => $row['tipo_de_beca'],
            'study_center' => $row['centro_de_estudio'],
            'payroll_disability_id' => $row['tipo_de_discapacidad'],
            'is_student' => $row['es_estudiante'],
            'has_disability' => $row['posee_una_discapacidad'],
            'has_scholarships' => $row['es_estudiante'] ? $row['posee_una_beca'] : false,
            'deleted_at' => null,
        ];
        DB::transaction(function () use ($row, $data) {
            if ($row['id'] and $row['es_trabajador']) {
                $payrollSocioeconomic = PayrollSocioeconomic::updateOrCreate(
                    ['payroll_staff_id' => $row["id"]],
                    ['marital_status_id' => $row["estado_civil_del_trabajador"]]
                );
            }

            if (!$row['es_trabajador'] and $row["id"]) {
                $payrollSocioeconomic = PayrollSocioeconomic::where('payroll_staff_id', $row["id"])->first();
                $whereCond = [
                    'payroll_socioeconomic_id' => $payrollSocioeconomic->id
                ];

                if ($row['tiene_cedula']) {
                    $whereCond['id_number'] = $row['cedula_del_pariente'];
                } else {
                    $whereCond['first_name'] = $row['nombres_del_pariente'];
                    $whereCond['last_name'] = $row['apellidos_del_pariente'];
                }

                $payrollFamilyBurden = PayrollFamilyBurden::updateOrCreate($whereCond, $data);
            }
        });
    }

    /**
     * Fila del encabezado
     *
     * @return integer
     */
    public function headingRow(): int
    {
        return 1;
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
        if (
            isset($data["cedula_del_trabajador"])
            && !empty($data["cedula_del_trabajador"])
            && !empty(trim($data["cedula_del_trabajador"]))
            && ctype_digit($data["cedula_del_trabajador"])
        ) {
            $usuario = PayrollStaff::where(['id_number' => $data["cedula_del_trabajador"]])->first();
            if ($usuario) {
                $data["id"] = $usuario->id;
            }
        }
        if (isset($data['estado_civil_del_trabajador']) and $data['parentesco'] === null) {
            $data['es_trabajador'] = true;
        } else {
            $data['es_trabajador'] = false;
        }
        //validamos si el estado civil es valido
        if (
            isset($data["estado_civil_del_trabajador"])
            && !empty($data["estado_civil_del_trabajador"])
            && !empty(trim($data["estado_civil_del_trabajador"]))
        ) {
            $maritalStatus = MaritalStatus::where(['name' => $data['estado_civil_del_trabajador']])->first();

            if ($maritalStatus) {
                $data['estado_civil_del_trabajador'] = $maritalStatus->id;
                $data['estado_civil_value'] = false;
            } else {
                $data['estado_civil_value'] = 'El estado civil ' . $data['estado_civil_del_trabajador'] . ' no existe';
            }
        }

        if (
            isset($data['parentesco'])
            && !empty($data['parentesco'])
            && !empty(trim($data['parentesco']))
        ) {
            $Relationships = PayrollRelationship::where(['name' => $data['parentesco']])->first();
            if ($Relationships) {
                $data['parentesco'] = $Relationships->id;
                $data['parentesco_value'] = false;
            } else {
                $data['parentesco_value'] = 'El parentesco ' . $data['parentesco'] . ' no existe';
            }
        }
        if (
            isset($data['nombres_del_pariente'])
            && !empty($data['nombres_del_pariente'])
            && !empty(trim($data['nombres_del_pariente']))
        ) {
            $data['nombres_del_pariente'] = trim($data['nombres_del_pariente']);
        } else {
            $data['nombres_del_pariente'] = null;
        }

        if (
            isset($data['apellidos_del_pariente'])
            && !empty($data['apellidos_del_pariente'])
            && !empty(trim($data['apellidos_del_pariente']))
        ) {
            $data['apellidos_del_pariente'] = trim($data['apellidos_del_pariente']);
        } else {
            $data['apellidos_del_pariente'] = null;
        }

        if (!empty($data['fecha_de_nacimiento'])) {
            if (is_numeric($data['fecha_de_nacimiento'])) {
                $data['fecha_de_nacimiento'] = Date::excelToDateTimeObject($data['fecha_de_nacimiento']);
            }
            try {
                $parsedDate = Carbon::parse($data['fecha_de_nacimiento']);
                if ($parsedDate->isValid()) {
                    $data['fecha_de_nacimiento_value'] = false;
                    $diffInYears = Carbon::today()->diffInYears($parsedDate);
                    $data['tiene_cedula'] = ($diffInYears > 11);
                } else {
                    $data['tiene_cedula'] = false;
                    $data['fecha_de_nacimiento_value'] = 'la fecha de nacimiento "' . $data['fecha_de_nacimiento'] . '" no tiene un formato valido';
                }
            } catch (\Exception $e) {
                $data['tiene_cedula'] = false;
                $data['fecha_de_nacimiento_value'] = 'la fecha de nacimiento "' . $data['fecha_de_nacimiento'] . '" no tiene un formato valido';
            }
        } else {
            $data['tiene_cedula'] = false;
            $data['fecha_de_nacimiento'] = null;
        }

        if (
            isset($data['direccion'])
            && !empty($data['direccion'])
            && !empty(trim($data['direccion']))
        ) {
            $data['direccion'] = trim($data['direccion']);
        } else {
            $data['direccion'] = null;
        }

        if (
            isset($data['genero'])
            && !empty($data['genero'])
            && !empty(trim($data['genero']))
        ) {
            $gender = Gender::where(['name' => $data['genero']])->first();
            $data['genero_value'] = 'el genero ' . $data['genero'] . ' no existe';
            if ($gender) {
                $data['genero_value'] = false;
                $data['genero'] = $gender->id;
            }
        }

        if (
            isset($data['es_estudiante'])
            && !empty($data['es_estudiante'])
            && !empty(trim($data['es_estudiante']))
        ) {
            $data['es_estudiante'] = ($data['es_estudiante'] === 'Si') ? true : (($data['es_estudiante'] === 'No') ? false : null);
            if ($data['es_estudiante'] === null) {
                $data['es_estudiante_value'] = 'el estado ' . $data['es_estudiante'] . ' no existe';
            }
        }

        if (
            isset($data['nivel_de_escolaridad'])
            && !empty($data['nivel_de_escolaridad'])
            && !empty(trim($data['nivel_de_escolaridad']))
        ) {
            $data['nivel_de_escolaridad_value'] = ' El nivel de escolaridad ' . $data['nivel_de_escolaridad'] . ' no existe';
            $schoolLevel = PayrollSchoolingLevel::where(['name' => $data['nivel_de_escolaridad']])->first();
            if ($schoolLevel) {
                $data['nivel_de_escolaridad'] = $schoolLevel->id;
                $data['nivel_de_escolaridad_value'] = false;
            }
        }

        if (
            isset($data['centro_de_estudio'])
            && !empty($data['centro_de_estudio'])
            && !empty(trim($data['centro_de_estudio']))
        ) {
            $data['centro_de_estudio'] = trim($data['centro_de_estudio']);
        }

        if (
            isset($data['posee_una_beca'])
            && !empty($data['posee_una_beca'])
            && !empty(trim($data['posee_una_beca']))
        ) {
            $data['posee_una_beca_value'] = false;
            $data['posee_una_beca'] = ($data['posee_una_beca'] === 'Si') ? true : (($data['posee_una_beca'] === 'No') ? false : null);
            if ($data['posee_una_beca'] === null) {
                $data['posee_una_beca_value'] = 'El estado ' . $data['posee_una_beca'] . ' no existe';
            }
        }

        if (
            isset($data['tipo_de_beca'])
            && !empty($data['tipo_de_beca'])
            && !empty(trim($data['tipo_de_beca']))
        ) {
            $data['tipo_de_beca_value'] = 'El tipo de beca ' . $data['tipo_de_beca'] . ' no existe';
            $scholarshipType = PayrollScholarshipType::where(['name' => $data['tipo_de_beca']])->first();
            if ($scholarshipType) {
                $data['tipo_de_beca'] = $scholarshipType->id;
                $data['tipo_de_beca_value'] = false;
            }
        }

        if (
            isset($data['posee_una_discapacidad'])
            && !empty($data['posee_una_discapacidad'])
            && !empty(trim($data['posee_una_discapacidad']))
        ) {
            $data['posee_una_discapacidad_value'] = false;
            $data['posee_una_discapacidad'] = ($data['posee_una_discapacidad'] === 'Si') ? true : (($data['posee_una_discapacidad'] === 'No') ? false : null);
            if ($data['posee_una_discapacidad'] === null) {
                $data['posee_una_discapacidad_value'] = 'El estado ' . $data['posee_una_discapacidad'] . ' no existe';
                ;
            }
        }

        if (
            isset($data['tipo_de_discapacidad'])
            && !empty($data['tipo_de_discapacidad'])
            && !empty(trim($data['tipo_de_discapacidad']))
        ) {
            $data['tipo_de_discapacidad_existe'] = 'El tipo de discapacidad ' . $data['tipo_de_discapacidad'] . ' no existe';
            $Disability = PayrollDisability::where(['name' => $data['tipo_de_discapacidad']])->first();
            if ($Disability) {
                $data['tipo_de_discapacidad'] = $Disability->id;
                $data['tipo_de_discapacidad_existe'] = false;
            }
        }

        return $data;
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['required'],
            'cedula_del_trabajador' => ['required', 'regex:/^([\d]{7}|[\d]{8})$/u'],
            'es_trabajador' => ['required', 'bool'],
            'estado_civil_del_trabajador' => ['required_if:es_trabajador,true'],
            'parentesco' => ['required_if:es_trabajador,false'],
            'nombres_del_pariente' => ['required_if:es_trabajador,false'],
            'apellidos_del_pariente' => ['required_if:es_trabajador,false'],
            'cedula_del_pariente' => [
                'required_if:tiene_cedula,true',
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return; // No se aplica la validación del regex si el valor está vacío
                    }

                    if (!preg_match('/^([\d]{7}|[\d]{8})$/', $value)) {
                        $fail('El formato de la cédula del pariente es inválido.');
                    }
                },
            ],
            'fecha_de_nacimiento' => ['required_if:es_trabajador,false | date'],
            'direccion' => ['required_if:es_trabajador,false'],
            'genero' => ['required_if:es_trabajador,false'],
            'es_estudiante' => ['required_if:es_trabajador,false'],
            'nivel_de_escolaridad' => ['required_if:es_estudiante,true'],
            'centro_de_estudio' => ['required_if:es_estudiante,true'],
            'posee_una_beca' => ['required_if:es_estudiante,true'],
            'tipo_de_beca' => ['required_if:posee_una_beca,true'],
            'posee_una_discapacidad' => ['required_if:es_trabajador,false'],
            'tipo_de_discapacidad' => ['required_if:posee_una_discapacidad,true'],
            'estado_civil_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'fecha_de_nacimiento_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'genero_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'es_estudiante_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'nivel_de_escolaridad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'centro_de_estudio_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'posee_una_beca_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'tipo_de_beca_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'posee_una_discapacidad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'tipo_de_discapacidad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
            'parentesco_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(strip_tags($value));
                }
            },
        ];
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
                'sheetName' => 'Datos Socioeconomicos'
            ];
            $jsonErrors = json_encode($validationErrors);
            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->fileErrosPath, $jsonErrors);
        }
    }

    /**
     * Mensajes personalizados de validación
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '*id.required' => 'Revise si se encuentran registrados los datos Personales de este trabajador en el sistema o si la cédula es la correcta',
            '*cedula_del_trabajador.required' => 'El campo :attribute es obligatorio.',
            '*cedula_del_trabajador.regex' => 'El formato del campo :attribute es incorrecto. Debe tener 7 o 8 caracteres.',
            '*direccion.required_if' => 'El campo dirección es obligatorio cuando se registra un pariente.',
            '*estado_civil_del_trabajador.required_if' => 'El campo :attribute es obligatorio cuando se registran los datos socioeconómicos del trabajador.',
            '*genero.required_if' => 'El campo Género es obligatorio cuando se registra un pariente.',
            '*parentesco.required_if' => 'El campo :attribute es obligatorio cuando se registra un pariente.',
            '*nombres_del_pariente.required_if' => 'El campo :attribute es obligatorio cuando se registra un pariente.',
            '*apellidos_del_pariente.required_if' => 'El campo :attribute es obligatorio cuando se registra un pariente.',
            '*cedula_del_pariente.required_if' => 'El campo :attribute es obligatorio si la edad del pariente es igual o mayor a 11 años',
            '*fecha_de_nacimiento.required_if' => 'El :attribute es obligatorio cuando se registra un pariente.',
            '*fecha_de_nacimiento.date' => 'El formato del campo :attribute es incorrecto.',
            '*nivel_de_escolaridad.required_if' => 'El campo :attribute es obligatorio cuando se registra un pariente que es estudiante.',
            '*centro_de_estudio.required_if' => 'El campo :attribute es obligatorio cuando se registra un pariente que es estudiante.',
            '*posee_una_beca.required_if' => 'El campo :attribute es obligatorio cuando el campo es_estudiante es Si.',
            '*tipo_de_beca.required_if' => 'El campo :attribute es obligatorio cuando el campo posee_una_beca es Si.',
            '*posee_una_discapacidad.required_if' => 'El campo :attribute es obligatorio cuando se registra un pariente.',
            '*tipo_de_discapacidad.required_if' => 'El campo :attribute es obligatorio cuando el campo posee_una_discapacidad es Si.',

        ];
    }
}
