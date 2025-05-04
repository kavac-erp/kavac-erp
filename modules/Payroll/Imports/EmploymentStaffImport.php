<?php

/**
 * Descripcion general
 */

namespace Modules\Payroll\Imports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Modules\Accounting\Models\Institution;
use Modules\Payroll\Models\PayrollContractType;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollPosition;
use Modules\Payroll\Models\PayrollCoordination;
use Modules\Payroll\Models\PayrollPositionType;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStaffType;
use Modules\Payroll\Models\PayrollPreviousJob;
use Modules\Payroll\Models\PayrollSectorType;
use Modules\Payroll\Models\PayrollInactivityType;
use Modules\Payroll\Models\Profile;
use Modules\Payroll\Rules\PayrollPositionRestriction;

/**
 * @class EmploymentStaffImport
 * @brief Importa un archivo de datos de personal
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class EmploymentStaffImport implements
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
     * Función para modelar datos para la importación del documento
     *
     * @param array $row Arreglo de columnas
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $date1 = new Carbon($row["fecha_de_ingreso_a_la_institucion"]);

        $today = Carbon::today();
        $years_apn = "";
        $years_apn_y = 0; // años acumulados en otros trabajos
        $years_apn_m = 0; // meses acumulados en otros trabajos
        $years_apn_d = 0; // dias acumulados en otros trabajos

        if ($row["esta_activo"]) {
            $today = Carbon::today();
            $intutionTimeToday = $today->diff($date1);
            $service_years = $intutionTimeToday->y;

            if ($intutionTimeToday->d > 0) {
                $institution_years = " Años:" . $intutionTimeToday->y . " Meses: " . $intutionTimeToday->m . " Días: " . $intutionTimeToday->d;
                $time_worked = " Años:" . $intutionTimeToday->y - 1 . " Meses: " . $intutionTimeToday->m . " Días: " . $intutionTimeToday->d;
            } else {
                $time_worked = 0;
            };
        } else {
            $dateEgresoInstitucion = new Carbon($row["fecha_de_egreso_de_la_institucion"]);

            $time_worked_institucion = $date1->diff($dateEgresoInstitucion);
            $service_years = $time_worked_institucion->y;

            if ($time_worked_institucion->d > 0) {
                $institution_years = " Años:" . $time_worked_institucion->y . " Meses: " . $time_worked_institucion->m . " Días: " . $time_worked_institucion->d;
                $time_worked = " Años:" . $time_worked_institucion->y - 1 . " Meses: " . $time_worked_institucion->m . " Días: " . $time_worked_institucion->d;
            } else {
                $time_worked = 0;
            };
        }

        for ($iter = 1; $iter <= 3; $iter++) {
            if (array_key_exists('nombre_de_la_organizacion_anterior_' . $iter, $row) && !is_null($row['nombre_de_la_organizacion_anterior_' . $iter])) {
                if ($row['tipo_de_sector' . $iter] == "Público") {
                    $fecha_de_inicio2 = new Carbon($row["fecha_de_inicio" . $iter]);
                    $fecha_de_cese2 = new Carbon($row["fecha_de_cese" . $iter]);
                    $timeDiff_2 = $fecha_de_inicio2->diff($fecha_de_cese2);
                    $years_apn_y = $years_apn_y + $timeDiff_2->y;
                    $years_apn_m = $years_apn_m + $timeDiff_2->m;
                    $years_apn_d = $years_apn_d + $timeDiff_2->d;

                    if ($years_apn_d > 30) {
                        $years_apn_d = $years_apn_d % 30;
                        $years_apn_m = $years_apn_m + 1;
                    }
                    if ($years_apn_m > 12) {
                        $years_apn_m = $years_apn_m % 12;
                        $years_apn_y = $years_apn_y + 1;
                    }
                    $years_apn = " Años:" . $years_apn_y - 1 . " Meses: " . $years_apn_d . " Días: " . $years_apn_y;
                    $service_years = $service_years + $years_apn_y;
                }
            }
        }

        DB::transaction(
            function () use ($row, $years_apn, $date1) {
                $payrollEmployment = PayrollEmployment::updateOrCreate(
                    [
                        'payroll_staff_id' => $row["id"] ?? null
                    ],
                    [
                        'years_apn' => $years_apn,
                        'start_date' => $date1,
                        'end_date' => $row["fecha_de_egreso_de_la_institucion"],
                        'active' => (('Si' === $row["esta_activo"]) || ('SI' === $row["esta_activo"])) ? true : false,
                        'payroll_inactivity_type_id' => $row["tipo_de_inactividad"],
                        'institution_email' => $row["correo_institucional"],
                        'function_description' => $row["descripcion_de_funciones"],
                        'payroll_position_type_id' => $row["tipo_de_cargo"],
                        'payroll_coordination_id' => $row["coordinacion"],
                        'payroll_staff_type_id' => $row["tipo_de_personal"],
                        'department_id' => $row["departamento"],
                        'payroll_contract_type_id' => $row["tipo_de_contrato"],
                        'worksheet_code' => $row["ficha_expediente"],
                    ]
                );

                // Crear el registro del cargo del trabajador en la tabla intermedia.
                $payrollEmployment->payrollPositions()->sync([$row["cargo"] => ['active' => true]]);

                for ($iter = 1; $iter <= 3; $iter++) {
                    if (array_key_exists('nombre_de_la_organizacion_anterior_' . $iter, $row) && !is_null($row['nombre_de_la_organizacion_anterior_' . $iter])) {
                        PayrollPreviousJob::updateOrCreate(
                            [
                                'payroll_employment_id' => $payrollEmployment->id,
                                'organization_name' => $row['nombre_de_la_organizacion_anterior_' . $iter],
                            ],
                            [
                                'organization_name' => $row['nombre_de_la_organizacion_anterior_' . $iter],
                                'organization_phone' => $row['telefono_de_la_organizacion' . $iter],
                                'payroll_sector_type_id' => $row['tipo_de_sector'  . $iter],
                                'previous_position' => $row['cargo'  . $iter],
                                'payroll_staff_type_id' => $row['tipo_de_personal' . $iter],
                                'start_date' => $row['fecha_de_inicio' . $iter],
                                'end_date' => $row['fecha_de_cese' . $iter],
                                'payroll_employment_id' => $payrollEmployment->id,
                            ]
                        );
                    } elseif (array_key_exists('nombre_de_la_organizacion_anterior' . $iter, $row) && !is_null($row['nombre_de_la_organizacion_anterior' . $iter])) {
                        PayrollPreviousJob::updateOrCreate(
                            [
                                'payroll_employment_id' => $payrollEmployment->id,
                                'organization_name' => $row['nombre_de_la_organizacion_anterior' . $iter],
                            ],
                            [
                                'organization_name' => $row['nombre_de_la_organizacion_anterior' . $iter],
                                'organization_phone' => $row['telefono_de_la_organizacion' . $iter],
                                'payroll_sector_type_id' => $row['tipo_de_sector'  . $iter],
                                'previous_position' => $row['cargo'  . $iter],
                                'payroll_staff_type_id' => $row['tipo_de_personal' . $iter],
                                'start_date' => $row['fecha_de_inicio' . $iter],
                                'end_date' => $row['fecha_de_cese' . $iter],
                                'payroll_employment_id' => $payrollEmployment->id,
                            ]
                        );
                    }
                }
                $payrollStaff = PayrollStaff::find($row["id"] ?? null);
                if (isset($payrollStaff)) {
                    Profile::updateOrCreate(
                        [
                            'first_name' => $payrollStaff->first_name,
                            'last_name' => $payrollStaff->last_name
                        ],
                        [
                            'first_name' => $payrollStaff->first_name, 'last_name' => $payrollStaff->last_name,
                            'institution_id' => $payrollEmployment->department->institution_id,
                            'employee_id' => $payrollEmployment->id,
                        ]
                    );
                }
            }
        );
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
        if (!empty($data["cedula_de_identidad"])) {
            $usuario = PayrollStaff::where(['id_number' => $data["cedula_de_identidad"],])->first();

            if (!empty($data["correo_institucional"])) {
                $employmentEmail = PayrollEmployment::query()
                    ->where('institution_email', $data["correo_institucional"])
                    ->where('id', '<>', $usuario?->payrollEmployment?->id)
                    ->get()
                    ->toBase();

                if (count($employmentEmail) > 0) {
                    $data['unique_email'] = true;
                }
            }

            if (!empty($data["ficha_expediente"])) {
                $employmentWorksheet = PayrollEmployment::query()
                    ->where('worksheet_code', $data["ficha_expediente"])
                    ->where('id', '<>', $usuario?->payrollEmployment?->id)
                    ->get()
                    ->toBase();

                if (count($employmentWorksheet) > 0) {
                    $data['unique_worksheet'] = true;
                }
            }

            if ($usuario) {
                $data["id"] = $usuario->id;
            }
        }

        $data["tipo_de_inactividad"] = (!isset($data["esta_activo"]) || !$data["esta_activo"] || is_null($data["esta_activo"])) ? 'pass' : $data["tipo_de_inactividad"];

        $institution = Institution::where('default', true)->first();
        $data["institution"] = $institution->id;
        $data["operation_date"] = $institution->start_operations_date;

        if (
            isset($data['fecha_de_ingreso_a_la_institucion']) &&
            $data['fecha_de_ingreso_a_la_institucion'] &&
            !is_null($data['fecha_de_ingreso_a_la_institucion']) &&
            trim($data['fecha_de_ingreso_a_la_institucion']) != '' &&
            is_numeric($data['fecha_de_ingreso_a_la_institucion'])
        ) {
            $data['fecha_de_ingreso_a_la_institucion'] = Date::excelToDateTimeObject($data['fecha_de_ingreso_a_la_institucion']);
        }
        if (
            isset($data['fecha_de_egreso_de_la_institucion']) &&
            $data['fecha_de_egreso_de_la_institucion'] &&
            !is_null($data['fecha_de_egreso_de_la_institucion']) &&
            trim($data['fecha_de_egreso_de_la_institucion']) != '' &&
            is_numeric($data['fecha_de_egreso_de_la_institucion'])
        ) {
            $data['fecha_de_egreso_de_la_institucion'] = Date::excelToDateTimeObject($data['fecha_de_egreso_de_la_institucion']);
        }

        if (isset($data['tipo_de_cargo']) && $data['tipo_de_cargo'] && !is_null($data['tipo_de_cargo']) && trim($data['tipo_de_cargo']) != '') {
            $modelID = false;
            $data['tipo_de_cargo_value'] = $data['tipo_de_cargo'];
            if (is_int($data['tipo_de_cargo']) && $data['tipo_de_cargo'] > 0) {
                $modelID = PayrollPositionType::where(['id' => $data['tipo_de_cargo'],])->first();
            }
            if (!$modelID && trim($data['tipo_de_cargo']) != '') {
                $modelID = PayrollPositionType::where(['name' => $data['tipo_de_cargo'],])->first();
            }
            if ($modelID) {
                $data['tipo_de_cargo'] = $modelID->id;
                $data['tipo_de_cargo_value'] = false;
            }
        }

        if (isset($data['tipo_de_contrato']) && $data['tipo_de_contrato'] && !is_null($data['tipo_de_contrato']) && trim($data['tipo_de_contrato']) != '') {
            $modelID = false;
            $data['tipo_de_contrato_value'] = $data['tipo_de_contrato'];
            if (is_int($data['tipo_de_contrato']) && $data['tipo_de_contrato'] > 0) {
                $modelID = PayrollContractType::where(['id' => $data['tipo_de_contrato'],])->first();
            }
            if (!$modelID && trim($data['tipo_de_contrato']) != '') {
                $modelID = PayrollContractType::where(['name' => $data['tipo_de_contrato'],])->first();
            }
            if ($modelID) {
                $data['tipo_de_contrato'] = $modelID->id;
                $data['tipo_de_contrato_value'] = false;
            }
        }
        if (isset($data['tipo_de_inactividad']) && $data['tipo_de_inactividad'] && !is_null($data['tipo_de_inactividad']) && trim($data['tipo_de_inactividad']) != '') {
            $modelID = false;
            $data['tipo_de_inactividad_value'] = $data['tipo_de_inactividad'];
            if (is_int($data['tipo_de_inactividad']) && $data['tipo_de_inactividad'] > 0) {
                $modelID = PayrollInactivityType::where(['id' => $data['tipo_de_inactividad'],])->first();
            }
            if (!$modelID && trim($data['tipo_de_inactividad']) != '') {
                $modelID = PayrollInactivityType::where(['name' => $data['tipo_de_inactividad'],])->first();
            }
            if ($modelID) {
                $data['tipo_de_inactividad'] = $modelID->id;
                $data['tipo_de_inactividad_value'] = false;
            }
        }
        if (isset($data['departamento']) && $data['departamento'] && !is_null($data['departamento']) && trim($data['departamento']) != '') {
            $modelID = false;
            $data['departamento_value'] = $data['departamento'];
            if (is_int($data['departamento']) && $data['departamento'] > 0) {
                $modelID = Department::where(['id' => $data['departamento'],])->first();
            }
            if (!$modelID && trim($data['departamento']) != '') {
                $modelID = Department::where(['name' => $data['departamento'],])->first();
            }
            if ($modelID) {
                $data['departamento'] = $modelID->id;
                $data['departamento_value'] = false;
            }
        }
        if ((isset($data['ficha_expediente']) && is_int($data['ficha_expediente']) && ($data['ficha_expediente'] > 0) && (mb_strlen($data['ficha_expediente']) == 5)) || is_null($data['ficha_expediente'])) {
            if (!is_null($data['ficha_expediente'])) {
                $results = PayrollEmployment::where('worksheet_code', $data['ficha_expediente'])->first();
                if (!isset($results)) {
                    $data['ficha_expediente_value'] = false;
                }
            } else {
                $data['ficha_expediente_value'] = false;
            }
        }

        for ($iter = 0; $iter <= 3; $iter++) {
            $key = $iter > 0 ? $iter : '';
            if (isset($data['fecha_de_inicio' . $key]) && $data['fecha_de_inicio' . $key] && !is_null($data['fecha_de_inicio' . $key]) && trim($data['fecha_de_inicio' . $key]) != '' && is_numeric($data['fecha_de_inicio' . $key])) {
                $data['fecha_de_inicio' . $key] = Date::excelToDateTimeObject($data['fecha_de_inicio' . $key]);
            }

            if (isset($data['fecha_de_cese' . $key]) && $data['fecha_de_cese' . $key] && !is_null($data['fecha_de_cese' . $key]) && trim($data['fecha_de_cese' . $key]) != '' && is_numeric($data['fecha_de_cese' . $key])) {
                $data['fecha_de_cese' . $key] = Date::excelToDateTimeObject($data['fecha_de_cese' . $key]);
            }

            if (
                isset($data['cargo' . $key])
                && $data['cargo' . $key]
                && !is_null($data['cargo' . $key])
                && trim($data['cargo' . $key])
                != ''
            ) {
                $modelID = false;
                $data['cargo' . $key . '_value'] = $data['cargo' . $key];
                if (is_int($data['cargo' . $key]) && $data['cargo' . $key] > 0) {
                    $modelID = PayrollPosition::where(['id' => $data['cargo' . $key],])->first();
                }
                if (!$modelID && trim($data['cargo' . $key]) != '') {
                    $modelID = PayrollPosition::where(['name' => $data['cargo' . $key],])->first();
                }
                if ($modelID) {
                    $data['cargo' . $key] = $modelID->id;
                    $data['cargo' . $key . '_value'] = false;
                }
            }

            if (isset($data['coordinacion' . $key]) && $data['coordinacion' . $key] && !is_null($data['coordinacion' . $key]) && trim($data['coordinacion' . $key]) != '') {
                $modelID = false;
                $data['coordinacion' . $key . '_value'] = $data['coordinacion' . $key];
                if (is_int($data['coordinacion' . $key]) && $data['coordinacion' . $key] > 0) {
                    $modelID = PayrollCoordination::where(['id' => $data['coordinacion' . $key],])->first();
                }
                if (!$modelID && trim($data['coordinacion' . $key]) != '') {
                    $modelID = PayrollCoordination::where(['name' => $data['coordinacion' . $key],])->first();
                }
                if ($modelID) {
                    $data['coordinacion' . $key] = $modelID->id;
                    $data['coordinacion' . $key . '_value'] = false;
                }
            }

            if (isset($data['tipo_de_personal' . $key]) && $data['tipo_de_personal' . $key] && !is_null($data['tipo_de_personal' . $key]) && trim($data['tipo_de_personal' . $key]) != '') {
                $modelID = false;
                $data['tipo_de_personal' . $key . '_value'] = $data['tipo_de_personal' . $key];
                if (is_int($data['tipo_de_personal' . $key]) && $data['tipo_de_personal' . $key] > 0) {
                    $modelID = PayrollStaffType::where(['id' => $data['tipo_de_personal' . $key],])->first();
                }
                if (!$modelID && trim($data['tipo_de_personal' . $key]) != '') {
                    $modelID = PayrollStaffType::where(['name' => $data['tipo_de_personal' . $key],])->first();
                }
                if ($modelID) {
                    $data['tipo_de_personal' . $key] = $modelID->id;
                    $data['tipo_de_personal' . $key . '_value'] = false;
                }
            }

            if (isset($data['tipo_de_sector' . $key]) && $data['tipo_de_sector' . $key] && !is_null($data['tipo_de_sector' . $key]) && trim($data['tipo_de_sector' . $key]) != '') {
                $modelID = false;
                $data['tipo_de_sector' . $key . '_value'] = $data['tipo_de_sector' . $key];
                if (is_int($data['tipo_de_sector' . $key]) && $data['tipo_de_sector' . $key] > 0) {
                    $modelID = PayrollSectorType::where(['id' => $data['tipo_de_sector' . $key],])->first();
                }
                if (!$modelID && trim($data['tipo_de_sector' . $key]) != '') {
                    $modelID = PayrollSectorType::where(['name' => $data['tipo_de_sector' . $key],])->first();
                }
                if ($modelID) {
                    $data['tipo_de_sector' . $key] = $modelID->id;
                    $data['tipo_de_sector' . $key . '_value'] = false;
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
                'sheetName' => 'Datos Laborales'
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
            'unique_email' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El correo institucional ya ha sido registrado para otro empleado.'
                    );
                }
            },
            'unique_worksheet' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'La ficha de expediente ya ha sido registrada para otro empleado.'
                    );
                }
            },
            'esta_activo' => ['required'],
            'cedula_de_identidad' => ['required'],
            'fecha_de_ingreso_a_la_institucion' => ['required', 'date', 'after:operation_date'],
            'fecha_de_engreso_a_la_institucion' => ['required_if:esta_activo,FALSE', 'date', 'after:fecha_de_ingreso_a_la_institucion', 'after:operation_date'],
            'tipo_de_inactividad' => ['required_if:esta_activo,FALSE', 'nullable'],
            'correo_institucional' => ['nullable', 'email'],
            'tipo_de_cargo' => ['required'],
            'cargo' => ['required', (new PayrollPositionRestriction())],
            'tipo_de_personal' => ['required'],
            'tipo_de_contrato' => ['required'],
            'departamento' => ['required'],
            'descripcion_de_funciones' => ['nullable', 'string'],
            'nombre_de_la_organizacion_anterior_1' => [
                'required_with:*.telefono_de_la_organizacion1,tipo_de_sector1,cargo1,tipo_de_personal1,fecha_de_inicio1,fecha_de_cese1',
                'nullable', 'string',
            ],
            'telefono_de_la_organizacion1' => [
                'required_with:*.nombre_de_la_organizacion_anterior_1,tipo_de_sector1,cargo1,tipo_de_personal1,fecha_de_inicio1,fecha_de_cese1',
                'nullable',
            ],
            'tipo_de_sector1' => [
                'required_with:*.telefono_de_la_organizacion1,nombre_de_la_organizacion_anterior_1,cargo1,tipo_de_personal1,fecha_de_inicio1,fecha_de_cese1',
                'nullable',
            ],
            'cargo1' => [
                'required_with:*.telefono_de_la_organizacion1,nombre_de_la_organizacion_anterior_1,tipo_de_sector1,tipo_de_personal1,fecha_de_inicio1,fecha_de_cese1',
                'nullable',
            ],
            'fecha_de_inicio1' => [
                'required_with:*.telefono_de_la_organizacion1,nombre_de_la_organizacion_anterior_1,tipo_de_sector1,cargo1,fecha_de_cese1',
                'nullable',
                'date',
            ],
            'fecha_de_cese1' => [
                'required_with:*.telefono_de_la_organizacion1,nombre_de_la_organizacion_anterior_1,tipo_de_sector1,cargo1,tipo_de_personal1,fecha_de_inicio1',
                'nullable',
                'date',
            ],

            'nombre_de_la_organizacion_anterior2' => [
                'required_with:*.telefono_de_la_organizacion2,tipo_de_sector2,cargo2,tipo_de_personal2,fecha_de_inicio2,fecha_de_cese2',
                'nullable',
                'string',
            ],
            'telefono_de_la_organizacion2' => [
                'required_with:*.nombre_de_la_organizacion_anterior2,tipo_de_sector2,cargo2,tipo_de_personal2,fecha_de_inicio2,fecha_de_cese2',
                'nullable',
            ],
            'tipo_de_sector2' => [
                'required_with:*.telefono_de_la_organizacion2,nombre_de_la_organizacion_anterior2,cargo2,tipo_de_personal2,fecha_de_inicio2,fecha_de_cese2',
                'nullable',
            ],
            'cargo2' => [
                'required_with:*.telefono_de_la_organizacion2,nombre_de_la_organizacion_anterior2,tipo_de_sector2,tipo_de_personal2,fecha_de_inicio2,fecha_de_cese2',
                'nullable',
            ],
            'fecha_de_inicio2' => [
                'required_with:*.telefono_de_la_organizacion2,nombre_de_la_organizacion_anterior2,tipo_de_sector2,cargo2,fecha_de_cese2',
                'nullable',
                'date',
            ],
            'fecha_de_cese2' => [
                'required_with:*.telefono_de_la_organizacion2,nombre_de_la_organizacion_anterior2,tipo_de_sector2,cargo2,tipo_de_personal2,fecha_de_inicio2',
                'nullable',
                'date',
            ],
            'nombre_de_la_organizacion_anterior3' => [
                'required_with:*.telefono_de_la_organizacion3,tipo_de_sector3,cargo3,tipo_de_personal3,fecha_de_inicio3,fecha_de_cese3',
                'nullable',
                'string',
            ],
            'telefono_de_la_organizacion3' => [
                'required_with:*.nombre_de_la_organizacion_anterior3,tipo_de_sector3,cargo3,tipo_de_personal3,fecha_de_inicio3,fecha_de_cese3',
                'nullable',
            ],
            'tipo_de_sector3' => [
                'required_with:*.telefono_de_la_organizacion3,nombre_de_la_organizacion_anterior3,cargo3,tipo_de_personal3,fecha_de_inicio3,fecha_de_cese3',
                'nullable',
            ],
            'cargo3' => [
                'required_with:*.telefono_de_la_organizacion3,nombre_de_la_organizacion_anterior3,tipo_de_sector3,tipo_de_personal3,fecha_de_inicio3,fecha_de_cese3',
                'nullable',
            ],
            'fecha_de_inicio3' => [
                'required_with:*.telefono_de_la_organizacion3,nombre_de_la_organizacion_anterior3,tipo_de_sector3,cargo3,fecha_de_cese3',
                'nullable',
                'date',
            ],
            'fecha_de_cese3' => [
                'required_with:*.telefono_de_la_organizacion3,nombre_de_la_organizacion_anterior3,tipo_de_sector3,cargo3,tipo_de_personal3,fecha_de_inicio3',
                'nullable',
                'date',
            ],
            'ficha_expediente' => [
                'nullable',
                'numeric',
                'min:0',
                'digits:5',
            ],
            'id' => ['required',],
            'tipo_de_cargo_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del tipo de cargo ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'cargo_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del cargo ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'coordinacion_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre de la coordinación ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_personal_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Personal ingresado (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_personal1_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Personal ingresado en la sección número 1 de trabajos anteriores (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_personal2_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Personal ingresado en la sección número 2 de trabajos anteriores (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_personal3_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Personal ingresado en la sección número 3 de trabajos anteriores (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_sector1_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Sector ingresado en la sección número 1 de trabajos anteriores (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_sector2_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Sector ingresado en la sección número 2 de trabajos anteriores (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_sector3_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de Sector ingresado en la sección número 3 de trabajos anteriores (' .
                            strip_tags($value) . ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_contrato_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de contrato ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'tipo_de_inactividad_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Tipo de inactividad ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'departamento_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre del Departamento ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
            'ficha_expediente_value' => function ($attribute, $value, $onFailure) {
                if ($value) {
                    $onFailure(
                        'El nombre de la Ficha de Expediente ingresado (' . strip_tags($value) .
                            ') no coincide con la lista disponible en la base de datos del sistema'
                    );
                }
            },
        ];
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
        ];
    }
}
