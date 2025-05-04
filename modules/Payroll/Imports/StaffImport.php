<?php

namespace Modules\Payroll\Imports;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Parish;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\PayrollBloodType;
use Modules\Payroll\Models\PayrollDisability;
use Modules\Payroll\Models\PayrollGender;
use Modules\Payroll\Models\PayrollLicenseDegree;
use Modules\Payroll\Models\PayrollNationality;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStaffUniformSize;
use Maatwebsite\Excel\Validators\Failure;

/**
 * @class StaffImport
 * @brief Importa un archivo de datos de personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class StaffImport implements
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
     * @param string $fileErrosPath Ruta del archivo de errores
     *
     * @return void
     */
    public function __construct(
        protected string $fileErrosPath,
    ) {
        //
    }

    /**
     * Modelo para importar los datos
     *
     * @param array $row Arreglo de columnas a importar
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function model(array $row)
    {
        if (!empty($row['tipo_de_sangre'])) {
            $blood_type_id = PayrollBloodType::firstOrCreate([
                'name' => $row['tipo_de_sangre'],
            ])->id;
        } else {
            $blood_type_id = null;
        }

        if (!empty($row['genero'])) {
            $gender_id = PayrollGender::firstOrCreate([
                'name' => $row['genero'],
            ])->id;
        }
        if (!empty($row['nacionalidad'])) {
            $nationality_id = PayrollNationality::firstOrCreate([
                'name' => $row['nacionalidad'],
            ])->id;
        }
        if (!empty($row['parroquia'])) {
            $parish_id = Parish::firstOrCreate([
                'name' => $row['parroquia'],
            ])->id;
        }
        /* Datos del personal */
        $data = [
          'email' => $row['correo_electronico'],
          'rif' => $row['rif'],
          'first_name' => $row['nombres'],
          'last_name' => $row['apellidos'],
          'birthdate' => !empty($row['fecha_de_nacimiento']) ? Carbon::createFromFormat('d-m-Y', $row['fecha_de_nacimiento'])->format('Y-m-d') : null,
          'address' => $row['direccion'],
          'payroll_blood_type_id' => $blood_type_id,
          'parish_id' => $parish_id,
          'payroll_nationality_id' => $nationality_id,
          'payroll_gender_id' => $gender_id,
          'deleted_at' => null,
          'passport' => $row['pasaporte'],
        ];

        if (!empty($row['historial_medico']) && trim($row['historial_medico'])) {
            $data['medical_history'] = strip_tags($row['historial_medico']);
        }
        if (!empty($row['posee_una_discapacidad'])) {
            $data['has_disability'] = match ($row['posee_una_discapacidad']) {
                'Si' => true,
                default => false,
            };
            if (!empty($row['discapacidad'])) {
                $data['payroll_disability_id'] = match ($row['posee_una_discapacidad']) {
                    'Si' => PayrollDisability::firstOrCreate([
                        'name' => $row['discapacidad'],
                    ])->id,
                    default => null,
                };
            }
        }
        if (!empty($row['posee_licencia_de_conducir'])) {
            $data['has_driver_license'] = match ($row['posee_licencia_de_conducir']) {
                'Si' => true,
                default => false,
            };
            if (!empty($row['grado_de_licencia'])) {
                $data['payroll_license_degree_id'] = match ($row['posee_licencia_de_conducir']) {
                    'Si' => PayrollLicenseDegree::firstOrCreate([
                        'name' => $row['grado_de_licencia'],
                    ])->id,
                    default => null,
                };
            }
        }
        if (!empty($row['seguro_social']) && trim($row['seguro_social']) != '') {
            $data['social_security'] = $row['seguro_social'];
        }
        if (!empty($row['nombre_y_apellido_de_persona_de_contacto']) && '' !== trim($row['nombre_y_apellido_de_persona_de_contacto'])) {
            $data['emergency_contact'] = $row['nombre_y_apellido_de_persona_de_contacto'];
        }
        if (!empty($row['telefono_de_persona_de_contacto']) && '' !== trim($row['telefono_de_persona_de_contacto'])) {
            $data['emergency_phone'] = $row['telefono_de_persona_de_contacto'];
        }
        $whereCond = [];
        if (!empty($row['id']) && trim($row['id']) != '') {
            $whereCond['id'] = $row['id'];
        } else {
            $whereCond['id_number'] = $row['cedula_de_identidad'];
        }

        $existStaff = PayrollStaff::where(['id_number' => $row['cedula_de_identidad']])->first();

        if (!$existStaff) {
            $codeSetting = CodeSetting::where('table', 'payroll_staffs')->first();
            if (!$codeSetting) {
                request()->session()->flash('message', [
                    'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente el formato para el código a generar',
                ]);
                return response()->json(['result' => false, 'redirect' => route('payroll.settings.index')], 200);
            }

            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])
                ->orderBy('year', 'desc')
                ->first();

            $data['code'] = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
                PayrollStaff::class,
                $codeSetting->field
            );
        }

        $payrollStaff = PayrollStaff::updateOrCreate($whereCond, $data);
        // cargamos los uniformes
        for ($iter = 1; $iter <= 3; $iter++) {
            if (isset($row['pieza_de_uniforme_' . $iter]) && !is_null($row['pieza_de_uniforme_' . $iter]) && isset($row['talla_' . $iter]) && !is_null($row['talla_' . $iter])) {
                foreach ($payrollStaff->payrollStaffUniformSize as $uniformSize) {
                    $uniformSize->delete();
                }
                continue;
            }
        }
        for ($iter = 1; $iter <= 3; $iter++) {
            if (isset($row['pieza_de_uniforme_' . $iter]) && !is_null($row['pieza_de_uniforme_' . $iter]) && isset($row['talla_' . $iter]) && !is_null($row['talla_' . $iter])) {
                $uniformSize = PayrollStaffUniformSize::create([
                'name' => $row['pieza_de_uniforme_' . $iter],
                'size' => $row['talla_' . $iter],
                'payroll_staff_id' => $payrollStaff->id,
                ]);
            }
        }
    }

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo de datos a importar
     * @param integer $index Índice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        $Emailusuario = PayrollStaff::where(['id_number' => trim($data['cedula_de_identidad']) ?? null])->first();
        $data['id'] = false;
        if (
            !is_null($data['cedula_de_identidad']) &&
            trim($data['cedula_de_identidad']) != '' &&
            $data['cedula_de_identidad'] != 'null' &&
            $data['cedula_de_identidad'] != null
        ) {
            $usuario = PayrollStaff::where(['id_number' => $data['cedula_de_identidad'],])->first();
            if ($usuario) {
                $data['id'] = $usuario->id;
                $data['code'] = $usuario->code;
            }
        }
        $usuarioFaultValues = [];

        if (isset($data['correo_electronico']) && !is_null($data['correo_electronico']) && trim($data['correo_electronico']) != '') {
            $usuario_email = PayrollStaff::query()
                ->where(['email' => $data['correo_electronico'],])
                ->where('id', '<>', $Emailusuario?->id ?? null)
                ->first();
            if ($usuario_email && $data['id'] && $data['id'] != $usuario_email->id) {
                $usuarioFaultValues[] = 'Ya existe otro usuario con el correo electronico ' . $usuario_email->email . ' (id = ' . $usuario_email->id . ', id_number = ' . $usuario_email->id_number . ')';
            }
        }

        if (!isset($data['pasaporte']) || is_null($data['pasaporte']) || trim($data['pasaporte']) == '') {
            $data['pasaporte'] = null;
        } else {
            $usuario_pasaport = PayrollStaff::query()
                ->where(['passport' => $data['pasaporte'],])
                ->where('id', '<>', $Emailusuario?->id ?? null)
                ->first();

            if ($usuario_pasaport) {
                $usuarioFaultValues[] = 'Ya existe otro usuario con el pasaporte ' . $usuario_pasaport->passport . ' (id = ' . $usuario_pasaport->id . ', id_number = ' . $usuario_pasaport->id_number . ')';
            }
        }

        if (isset($data['rif']) && !is_null($data['rif']) && trim($data['rif']) != '') {
            $data['rif'] = preg_replace('/[^a-zA-Z0-9]/', '', $data['rif']);
            $usuario_rif = PayrollStaff::query()
                ->where(['rif' => $data['rif'],])
                ->where('id', '<>', $Emailusuario?->id ?? null)
                ->first();
            if ($usuario_rif && $data['id'] && $data['id'] != $usuario_rif->id) {
                $usuarioFaultValues[] = 'Ya existe otro usuario con el rif ' . $usuario_email->rif . ' (id = ' . $usuario_email->id . ', id_number = ' . $usuario_email->id_number . ')';
            }
        }

        $data['data_usuario_exist_value'] = count($usuarioFaultValues) ? implode(', ', $usuarioFaultValues) : false;
        $data['data_code_value'] = false;
        if (
            !isset($data['code']) ||
            is_null($data['code']) ||
            trim($data['code']) == '' ||
            $data['code'] == 'null' ||
            $data['code'] == null
        ) {
            $codeSetting = CodeSetting::where('table', 'payroll_staffs')->first();
            if ($codeSetting) {
                $year = $codeSetting->format_year === '2' ? date('y') : date('Y');
                if (isset($currentFiscalYear) && $currentFiscalYear instanceof FiscalYear) {
                    $year = $codeSetting->format_year === '2' ? substr($currentFiscalYear->year, 2, 2) : $currentFiscalYear->year;
                }
                $data['code'] = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    $year,
                    PayrollStaff::class,
                    $codeSetting->field
                );
            } else {
                $data['data_code_value'] = true;
            }
        }

        for ($iter = 1; $iter <= 3; $iter++) {
            if (
                !isset($data['pieza_de_uniforme_' . $iter]) ||
                is_null($data['pieza_de_uniforme_' . $iter]) ||
                trim($data['pieza_de_uniforme_' . $iter]) == '' ||
                $data['pieza_de_uniforme_' . $iter] == 'null' ||
                $data['pieza_de_uniforme_' . $iter] == null
            ) {
                $data['pieza_de_uniforme_' . $iter] = null;
            }
        }

        $parameter = Parameter::where([
          'active' => true,
          'required_by' => 'payroll',
          'p_key' => 'work_age',
          ])->first();
        if (
            isset($data['fecha_de_nacimiento']) &&
            !is_null($data['fecha_de_nacimiento']) &&
            $data['fecha_de_nacimiento'] != '' &&
            $data['fecha_de_nacimiento'] != 'null' &&
            $data['fecha_de_nacimiento'] != null
        ) {
            $data['fecha_de_nacimiento_value'] = false;
            if (is_numeric($data['fecha_de_nacimiento'])) {
                $data['fecha_de_nacimiento'] = Date::excelToDateTimeObject($data['fecha_de_nacimiento']);
            }
            $date = new Carbon($data['fecha_de_nacimiento']);
            $today = Carbon::today();
            $age = $today->diff($date);
            $year = $age->y;

            if ($parameter->p_value > $age->y) {
                $data['fecha_de_nacimiento_value'] = $data['fecha_de_nacimiento'] . ' >= ' . $parameter->p_value;
            }
        } else {
            $data['fecha_de_nacimiento_value'] = $data['fecha_de_nacimiento'] . ' >= ' . $parameter->p_value;
        }

        return $data;
    }

    /**
     * Callback de error de validación.
     *
     * @param mixed $failures Arreglo columnas que fallaron en la validación
     *
     * @return void
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $validationErrors = [
                'row' => $failure->row(),
                'attribute' => str_replace('_value', '', $failure->attribute()),
                'error' => $failure->errors()[0],
                'sheetName' => 'Datos Personales'
            ];
            $jsonErrors = json_encode($validationErrors);
            \Illuminate\Support\Facades\Storage::disk('temporary')->append($this->fileErrosPath, $jsonErrors);
        }
    }

    /**
     * Reglas de validación.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fecha_de_nacimiento' => ['required', 'date'],
            'nombres' => ['required'],
            'nacionalidad' => ['required'],
            'cedula_de_identidad' => ['required', 'regex:/^([\d]{7}|[\d]{8})$/u'],
            'rif' => ['required', 'regex:/^[E, G, J, P, V, 0-9 ]+$/', 'size:10'],
            'direccion' => ['nullable'],
            'pasaporte' => ['nullable', 'min:5', 'max:20'],
            'correo_electronico' => ['nullable', 'email'],
            'genero' => ['required'],
            'parroquia' => ['required'],
            'discapacidad' => ['required_if:posee_una_discapacidad,TRUE'],
            'pieza_de_uniforme_1' => ['required_with:*.talla_1'],
            'talla_1' => ['required_with:*.pieza_de_uniforme_1'],
            'pieza_de_uniforme_3' => ['required_with:*.talla_3'],
            'talla_3' => ['required_with:*.pieza_de_uniforme_3'],
            'pieza_de_uniforme_2' => ['required_with:*.talla_2'],
            'talla_2' => ['required_with:*.pieza_de_uniforme_2'],
            'tipo_de_sangre' => ['nullable'],
            'grado_de_licencia' => ['required_if:posee_licencia_de_conducir,TRUE'],
            'data_usuario_exist_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('Existen registros duplicados: ' . strip_tags($value));
                }
            },
            'data_code_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure('Debe configurar previamente el formato para el código a generar');
                }
            },
            'nacionalidad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'La nacionalidad ingresada (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'genero_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Género ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'discapacidad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre de la Discapacidad ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_sangre_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Sangre ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'grado_de_licencia_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El grado de licencia ingresado (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'parroquia_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'La Parroquia ingresada (' . strip_tags($value) .
                        ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'fecha_de_nacimiento_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'La Edad del trabajador (' . strip_tags($value) .
                        ') debe ser mayor o igual a la  minima para trabajar'
                    );
                }
            },
        ];
    }
}
