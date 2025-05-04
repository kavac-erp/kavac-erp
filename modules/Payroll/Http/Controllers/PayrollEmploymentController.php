<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Rules\DateBeforeFiscalYear;
use Modules\Payroll\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Models\Institution;
use Modules\Payroll\Models\PayrollStaff;
use App\Notifications\SystemNotification;
use Modules\Payroll\Models\PayrollPosition;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollPreviousJob;
use Modules\Payroll\Models\PayrollSupervisedGroup;
use Modules\Payroll\Jobs\PayrollExportNotification;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSupervisedGroupStaff;

/**
 * @class PayrollEmploymentController
 * @brief Controlador de datos laborales
 *
 * Clase que gestiona los datos laborales
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollEmploymentController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación de formulario
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Attributos de la validación
     *
     * @var array $attributes
     */
    protected $attributes;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.employments.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.employments.create', ['only' => 'store']);
        $this->middleware('permission:payroll.employments.edit', ['only' => ['create', 'update']]);
        $this->middleware('permission:payroll.employments.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.employments.import', ['only' => 'import']);
        $this->middleware('permission:payroll.employments.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->rules = [
            //'years_apn' => ['max:2'],
            'start_date' => ['required', 'date', new DateBeforeFiscalYear('Fecha de ingreso')],
            'end_date' => ['nullable', 'date', new DateBeforeFiscalYear('Fecha de egreso')],
            'function_description' => ['nullable'],
            'payroll_position_type_id' => ['required'],
            'payroll_position_id' => ['required',],
            'payroll_staff_type_id' => ['required'],
            'institution_id' => ['required'],
            'department_id' => ['required'],
            'payroll_contract_type_id' => ['required'],
            'previous_jobs' => ['sometimes', 'array'],
            'previous_jobs.*.start_date' => [
                'sometimes',
                'before:start_date',
                'before:previous_jobs.*.end_date'
            ],
            'previous_jobs.*.end_date' => [
                'sometimes',
                'before:start_date',
                'after:previous_jobs.*.start_date'
            ],
            'previous_jobs.*.previous_position' => [
                'sometimes',
                'required',
                'max:300'
            ],
            'worksheet_code' => [
                'nullable',
                'numeric',
                'min:0',
                'digits:5',
                'unique:payroll_employments,worksheet_code'
            ],
        ];

        /* Define los atributos para los campos personalizados */
        $this->attributes = [
            'years_apn' => 'años en otras instituciones públicas',
            'start_date' => 'fecha de ingreso a la institución',
            'end_date' => 'fecha de egreso de la institución',
            'function_description' => 'descripción de funciones',
            'payroll_position_type_id' => 'tipo de cargo',
            'payroll_position_id' => 'cargo',
            'payroll_coordination_id' => 'coordinación',
            'payroll_staff_type_id' => 'tipo de personal',
            'institution_id' => 'institución',
            'department_id' => 'departamento',
            'payroll_contract_type_id' => 'tipo de contracto',
            'payroll_staff_id' => 'trabajador',
            //'institution_email' => 'correo institucional',
            'previous_jobs.*.start_date' => 'fecha de inicio',
            'previous_jobs.*.end_date' => 'fecha de cese',
            'previous_jobs.*.previous_position' => 'cargo',
            'worksheet_code' => 'ficha',
        ];
    }

    /**
     * Muestra todos los registros de datos laborales
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View    Muestra los datos organizados en una tabla
     */
    public function index()
    {
        return view('payroll::employments.index');
    }

    /**
     * Muestra el formulario de registro de datos laborales
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View    Vista con el formulario
     */
    public function create()
    {
        return view('payroll::employments.create-edit');
    }

    /**
     * Valida y registra un nuevo dato laboral
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    Json: result en verdadero y redirect con la url a donde ir
     */
    public function store(Request $request)
    {
        $institution = Institution::whereId($request->institution_id)->first();

        $this->rules['payroll_staff_id'] = [
            'required',
            'unique:payroll_employments,payroll_staff_id'
        ];

        if (isset($institution)) {
            if ($request->start_date) {
                $this->rules['start_date'] = [
                    'after_or_equal:' . $institution->start_operations_date
                ];
            }

            if ($request->end_date) {
                $this->rules['start_date'] = [
                    'before:end_date',
                    'after_or_equal:' . $institution->start_operations_date
                ];

                $this->rules['end_date'] = [
                    'after:start_date'
                ];
            }
        }

        if (!$request->active) {
            $this->validate(
                $request,
                [
                    'payroll_inactivity_type_id' => ['required'],
                ],
                [],
                [
                    'payroll_inactivity_type_id' => 'tipo de inactividad',
                ],
            );
        }

        if ($request->institution_email) {
            $this->validate(
                $request,
                [
                    'institution_email' => [
                        'email',
                        'unique:payroll_employments,institution_email'
                    ],
                ],
            );
        }
        $this->validate($request, $this->rules, [], $this->attributes);

        /* Obtener el valor de number_positions_assigned del PayrollPosition
        relacionado */
        $position = PayrollPosition::find($request->payroll_position_id);
        $numberPositionsAssigned = $position->number_positions_assigned
            ? $position->number_positions_assigned : 0;

        /* Contar cuántos registros de PayrollEmployment ya están relacionados
        con el cargo y tienen active true */
        $existingEmploymentsCount = $position->payrollEmployments()
            ->wherePivot('active', true)
            ->count();

        if ($numberPositionsAssigned - $existingEmploymentsCount <= 0) {
            $positionAvailableValidation = true;
            $this->validate(
                $request,
                [
                    'payroll_position_id' => [
                        $positionAvailableValidation ? function ($attribute, $value, $fail) {
                            if ($value) {
                                $fail(
                                    'No hay disponibilidad de asignación para el cargo seleccionado'
                                );
                            }
                        } : [],
                    ],
                ],
            );
        } else {
            $positionAvailableValidation = false;
        }

        $payrollEmployment = PayrollEmployment::create([
            'payroll_staff_id' => $request->payroll_staff_id,
            'years_apn' => $request->years_apn,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'active' => !empty($request->active) ? $request->active : false,
            'payroll_inactivity_type_id' => (!$request->active)
                ? $request->payroll_inactivity_type_id : null,
            'institution_email' => (!is_null($request->institution_email))
                ? $request->institution_email : null,
            'function_description' => $request->function_description,
            'payroll_position_type_id' => $request->payroll_position_type_id,
            'payroll_coordination_id' => $request->payroll_coordination_id,
            'payroll_staff_type_id' => $request->payroll_staff_type_id,
            'department_id' => $request->department_id,
            'payroll_contract_type_id' => $request->payroll_contract_type_id,
            'worksheet_code' => (!is_null($request->worksheet_code))
                ? $request->worksheet_code : null,
        ]);

        /* Crear el registro del cargo del trabajador en la tabla intermedia.
        y evalua si se libera el cargo del trabajador o no */
        $payrollEmployment->payrollPositions()->attach(
            $request->payroll_position_id,
            [
                'active' => !$request->release_charge,
                'created_at' => now(),
            ]
        );

        if ($request->previous_jobs && !empty($request->previous_jobs)) {
            foreach ($request->previous_jobs as $job) {
                $previousJob = PayrollPreviousJob::create([
                    'organization_name'      => $job['organization_name'],
                    'organization_phone'     => $job['organization_phone'],
                    'payroll_sector_type_id' => $job['payroll_sector_type_id'],
                    'previous_position'      => $job['previous_position'],
                    'payroll_staff_type_id'  => $job['payroll_staff_type_id'],
                    'start_date'             => $job['start_date'],
                    'end_date'               => $job['end_date'],
                    'payroll_employment_id'  => $payrollEmployment->id
                ]);
            }
        }

        // Registrar ciertos datos del perfil
        $payrollStaff = PayrollStaff::find($request->payroll_staff_id);
        $profile = Profile::create([
            'first_name' => $payrollStaff->first_name,
            'last_name' => $payrollStaff->last_name,
            'institution_id' => $payrollEmployment->department->institution_id,
            'employee_id' => $payrollEmployment->id,
        ]);

        $request->session()->flash('message', ['type' => 'store']);

        return response()->json([
            'result' => true,
            'redirect' => route('payroll.employments.index')
        ], 200);
    }

    /**
     * Muestra los datos de un dato laboral en específico
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse    Json con el dato laboral
     */
    public function show($id)
    {
        $payrollEmployment = PayrollEmployment::where('id', $id)->with([
            'payrollPreviousJob',
            'payrollStaff' => function ($query) {
                $query->with(
                    'payrollNationality',
                    'payrollGender',
                    'payrollLicenseDegree',
                    'payrollBloodType',
                    'payrollDisability'
                );
            },
            'payrollInactivityType',
            'payrollPositionType',
            'payrollPositions',
            'payrollCoordination',
            'payrollStaffType',
            'department',
            'payrollContractType'
        ])->first();

        return response()->json([
            'record' => $payrollEmployment,
            'age' => age($payrollEmployment->payrollStaff->birthdate)
        ], 200);
    }

    /**
     * Muestra el formulario de actualización de dato laboral
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View    Vista con el formulario y el objeto a actualizar
     */
    public function edit($id)
    {
        $payrollEmployment = PayrollEmployment::find($id);
        return view('payroll::employments.create-edit', compact('payrollEmployment'));
    }

    /**
     * Actualiza el dato laboral
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse    Json con la redirección y mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollEmployment = PayrollEmployment::with('payrollPreviousJob')->find($id);

        /* Validación de que no pueda actualizar un nuevo trabajador si no hay cargos disponibles */

        /* Obtener el valor de number_positions_assigned del PayrollPosition
        relacionado */
        $position = PayrollPosition::find($request->payroll_position_id);
        if ($position) {
            $numberPositionsAssigned = $position->number_positions_assigned;

            /* Contar cuántos registros de PayrollEmployment ya están relacionados
            con el cargo y tienen active true */
            $existingEmploymentsCount = $position->payrollEmployments()
                ->wherePivot('active', true)
                ->count();

            /* Se valida si el cargo que se está actualizando en la tabla intermedia ya está asociado al usuario que se esta actualizando. */
            $match = $payrollEmployment->payrollPositions()->where(
                'payroll_position_id',
                $request->payroll_position_id
            )->exists();

            if (!$match) {
                if ($numberPositionsAssigned - $existingEmploymentsCount <= 0) {
                    $positionAvailableValidation = true;
                    $this->validate(
                        $request,
                        [
                            'payroll_position_id' => [
                                $positionAvailableValidation ? function ($attribute, $value, $fail) {
                                    if ($value) {
                                        $fail(
                                            'No hay disponibilidad de asignación para el cargo seleccionado'
                                        );
                                    }
                                } : [],
                            ],
                        ],
                    );
                } else {
                    $positionAvailableValidation = false;
                }
            }
        }

        $request->institution_id ? $institution = Institution::whereId(
            $request->institution_id
        )->first() : $this->validate(
            $request,
            $this->rules,
            [],
            $this->attributes
        );

        $this->rules['payroll_staff_id'] = [
            'required',
            'unique:payroll_employments,payroll_staff_id,'
                . $payrollEmployment->id
        ];

        if ($request->worksheet_code) {
            $this->rules['worksheet_code'] =
                [
                    'nullable',
                    'numeric',
                    'min:0',
                    'digits:5',
                    Rule::unique('payroll_employments')->ignore($id)
                ];
        }

        if ($request->institution_email) {
            $this->rules['institution_email'] = [
                'email',
                'unique:payroll_employments,institution_email,'
                    . $payrollEmployment->id
            ];
        }

        if ($request->start_date) {
            $this->rules['start_date'] = [
                'after_or_equal:' . $institution->start_operations_date,
                new DateBeforeFiscalYear('Fecha de egreso')
            ];
        }

        if ($request->end_date) {
            $this->rules['start_date'] = ['before:end_date', new DateBeforeFiscalYear('Fecha de egreso')];
            $this->rules['end_date'] = ['after:start_date', new DateBeforeFiscalYear('Fecha de egreso')];
        }

        if (!$request->active) {
            $this->validate(
                $request,
                [
                    'payroll_inactivity_type_id' => ['required'],
                ],
                [],
                [
                    'payroll_inactivity_type_id' => 'tipo de inactividad',
                ],
            );
        }
        $this->validate($request, $this->rules, [], $this->attributes);

        if ($payrollEmployment->active == true && $request->active == false) {
            $supervisedGroupStaff = PayrollSupervisedGroupStaff::query()
                ->where('payroll_staff_id', $request->payroll_staff_id)
                ->first();

            $supervisedGroupApprover = PayrollSupervisedGroup::query()
                ->where('approver_id', $request->payroll_staff_id)
                ->first();

            $supervisedGroupSupervisor = PayrollSupervisedGroup::query()
                ->where('supervisor_id', $request->payroll_staff_id)
                ->first();

            if ($supervisedGroupStaff) {
                $profile = Profile::query()
                    ->with('user')
                    ->has('user')
                    ->where(
                        'employee_id',
                        $supervisedGroupStaff->payrollSupervisedGroup->supervisor_id
                    )
                    ->first();

                if ($profile) {
                    $profile->user->notify(
                        new SystemNotification(
                            'Aviso',
                            $payrollEmployment->payrollStaff->full_name
                                . ' se ha colocado como inactivo,'
                                . 'por favor actualice su grupo de supervisados.'
                        )
                    );
                }
            }

            if ($supervisedGroupApprover || $supervisedGroupSupervisor) {
                $profiles = Profile::query()
                    ->with('user')
                    ->has('user')
                    ->get()
                    ->filter(function ($profile) {
                        return $profile->user->hasRole('payroll');
                    });

                foreach ($profiles as $profile) {
                    if ($profile) {
                        $profile->user->notify(
                            new SystemNotification(
                                'Aviso',
                                $payrollEmployment->payrollStaff->full_name
                                    . ' se ha colocado como inactivo,' .
                                    'por favor actualice su grupo de supervisados.'
                            )
                        );
                    }
                }
            }
        }

        $payrollEmployment->payroll_staff_id  = $request->payroll_staff_id;
        $payrollEmployment->years_apn = $request->years_apn;
        $payrollEmployment->start_date = $request->start_date;
        $payrollEmployment->end_date = $request->end_date;
        $payrollEmployment->active = !empty($request->active) ? $request->active : false;
        $payrollEmployment
            ->payroll_inactivity_type_id = (!$request->active)
            ? $request->payroll_inactivity_type_id : null;
        $payrollEmployment->institution_email = (!is_null($request->institution_email))
            ? $request->institution_email : null;
        $payrollEmployment->function_description = $request->function_description;
        $payrollEmployment->payroll_position_type_id = $request->payroll_position_type_id;
        $payrollEmployment->payroll_coordination_id = $request->payroll_coordination_id;
        $payrollEmployment->payroll_staff_type_id = $request->payroll_staff_type_id;
        $payrollEmployment->department_id = $request->department_id;
        $payrollEmployment->payroll_contract_type_id = $request->payroll_contract_type_id;
        $payrollEmployment->worksheet_code = $request->worksheet_code;

        /* Actualiza el registro del cargo y el active del trabajador en la
        tabla intermedia. */
        $active = !$request->release_charge;
        $payrollEmployment->payrollPositions()->sync([
            $request->payroll_position_id => [
                'active' => $active
            ]
        ]);

        // Desactivar el usuario si se desactiva el empleado.
        if (!$request->active) {
            $employment = PayrollEmployment::findOrFail($id);

            // Verificar si existe un perfil asociado al empleado.
            if ($employment->profile) {
                // Obtén el usuario asociado al perfil.
                $user = $employment->profile->user;

                if ($user) {
                    // Desactivar el usuario relacionado con el empleado.
                    $user->active = false;
                    $user->save();
                }
            }
        }

        $payrollEmployment->save();

        foreach ($payrollEmployment->PayrollPreviousJob as $job) {
            $job->delete();
        }

        if ($payrollEmployment->PayrollPreviousJob == true) {
            foreach ($request->previous_jobs as $job) {
                $previousJob = PayrollPreviousJob::create([
                    'organization_name'      => $job['organization_name'],
                    'organization_phone'     => $job['organization_phone'],
                    'payroll_sector_type_id' => $job['payroll_sector_type_id'],
                    'previous_position'      => $job['previous_position'],
                    'payroll_staff_type_id'  => $job['payroll_staff_type_id'],
                    'start_date'             => $job['start_date'],
                    'end_date'               => $job['end_date'],
                    'payroll_employment_id'  => $payrollEmployment->id
                ]);
            }
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true,
            'redirect' => route('payroll.employments.index')
        ], 200);
    }

    /**
     * Realiza la acción necesaria para importar los datos Laborales
     *
     * @author    Francisco Escala
     *
     * @param    \Illuminate\Http\Request  $request    Objeto con la información de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function import(Request $request)
    {
        $filePath = $request->file('file')->store('', 'temporary');
        $fileErrorsPath = 'import' . uniqid() . '.errors';
        Storage::disk('temporary')->put($fileErrorsPath, '');
        $import = new RegisterStaffImport($filePath, 'temporary', auth()->user()->id, $fileErrorsPath);

        $import->import();

        return response()->json(['result' => true], 200);
    }

    /**
     * Exportar registros
     *
     * @author  Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        $userId = auth()->user()->id;
        PayrollExportNotification::dispatch(
            $userId,
            'Datos Laborales',
        );

        request()->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
                'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.employments.index');
    }

    /**
     * Elimina el dato laboral
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollEmployment = PayrollEmployment::find($id);

        // Obtén el ID del del empleado
        $payrollPositionId = $payrollEmployment->payrollPositions->first()->id;

        /* actualizar active como false para que libere el carto antes de
        eliminarse el empleado */
        $payrollEmployment->payrollPositions()->updateExistingPivot(
            $payrollPositionId,
            ['active' => false]
        );

        $payrollEmployment->delete();

        $payrollPreviousJob = PayrollPreviousJob::where('id', $payrollEmployment->id)->get();

        foreach ($payrollPreviousJob as $job) {
            $job->delete();
        }

        return response()->json(['record' => $payrollEmployment, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los datos laborales registrados
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos laborales del trabajador
     */
    public function vueList(Request $request)
    {
        $records = PayrollEmployment::query()
            ->select('*', DB::raw("CASE WHEN active THEN 'SI' ELSE 'NO' END AS is_active"))
            ->with([
                'payrollStaff' => function ($query) {
                    $query->select(
                        'id',
                        'first_name',
                        'last_name',
                        'id_number',
                        'email'
                    )
                        ->without(
                            'payrollEmployment',
                            'payrollSocioeconomic',
                            'payrollFinancial',
                            'payrollProfessional',
                            'payrollStaffUniformSize'
                        );
                },
                'payrollInactivityType',
                'payrollPositionType' => function ($query) {
                    $query->select('id', 'name', 'description');
                },
                'payrollPositions' => function ($query) {
                    $query->select('name', 'description', 'responsible');
                },
                'payrollCoordination',
                'payrollStaffType' => function ($query) {
                    $query->select('id', 'name', 'description');
                },
                'department' => function ($query) {
                    $query->select('id', 'name', 'institution_id')->with([
                        'institution' => function ($query) {
                            $query->select('id', 'acronym', 'name');
                        }
                    ]);
                },
                'payrollContractType' => function ($query) {
                    $query->select('id', 'name');
                },
                'payrollPreviousJob' => function ($query) {
                    $query->select(
                        'id',
                        'organization_name',
                        'organization_phone',
                        'payroll_sector_type_id',
                        'payroll_staff_type_id',
                        'previous_position',
                        'start_date',
                        'end_date',
                        'payroll_employment_id'
                    )->with([
                        'payrollPosition',
                        'payrollStaffType' => function ($q) {
                            $q->select('id', 'name', 'description');
                        },
                        'payrollSectorType' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ]);
                }
            ])
            ->search($request->get('query'))
            ->paginate($request->get('limit'));

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])
            ->orderBy('year', 'desc')->first();

        return response()->json(
            [
                'data' => $records->items(),
                'count' => $records->total(),
            ],
            200
        );
    }

    /**
     * Devuelve un listado de los registros almacenados en la tabla intermedia
     * payroll_employment_payroll_position.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de las coordinaciones.
     */
    public function getEmploymentsPositions()
    {
        $data = DB::table('payroll_employment_payroll_position')->get();

        return response()->json([
            'record' => $data,
        ], 200);
    }
}
