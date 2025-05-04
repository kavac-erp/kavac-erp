<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Phone;
use App\Models\Profile;
use App\Models\User;
use App\Rules\AgeToWork;
use App\Rules\Rif as RifRule;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollProfessional;
use Modules\Payroll\Models\PayrollSocioeconomic;
use Modules\Payroll\Models\PayrollFinancial;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStaffUniformSize;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Modules\Payroll\Jobs\PayrollExportNotification;

/**
 * @class PayrollStaffController
 * @brief Controlador de la información personal del trabajador
 *
 * Clase que gestiona la información personal del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Atributos de las reglas de validación
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
        $this->middleware('permission:payroll.staffs.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.staffs.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.staffs.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.staffs.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.staffs.import', ['only' => 'import']);
        $this->middleware('permission:payroll.staffs.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->rules = [
            'first_name' => ['required', 'max:100'],
            'last_name' => ['required', 'max:100'],
            'payroll_nationality_id' => ['required'],
            'id_number' => [],
            'passport' => [],
            'rif' => [
                'required',
                'regex:/^[E, G, J, P, V, 0-9 ]+$/',
                'size:10',
                new RifRule(),
                Rule::unique('payroll_staffs', 'rif')
            ],
            'email' => ['nullable', 'unique:payroll_staffs,email', 'email'],
            'birthdate' => [],
            'payroll_gender_id' => ['required'],
            'emergency_contact' => ['nullable'],
            'emergency_phone' => ['nullable', 'regex:/^\+\d{2}-\d{3}-\d{7}$/u'],
            'payroll_blood_type_id' => ['nullable'],
            'social_security' => ['nullable', 'max:20'],
            'country_id' => ['required'],
            'estate_id' => ['required'],
            'municipality_id' => ['required'],
            'parish_id' => ['required'],
            'address' => ['nullable', 'max:200'],
            'medical_history' => ['nullable'],
            'uniform_sizes.*.size' => ['sometimes', 'required'],
            'uniform_sizes.*.name' => ['sometimes', 'required'],
        ];

        /* Define los atributos para los campos personalizados*/
        $this->attributes = [
            'id_number' => 'cédula de identidad',
            'birthdate' => 'fecha de nacimiento',
            'emergency_phone' => 'télefono de contacto',
            'payroll_nationality_id' => 'nacionalidad',
            'payroll_gender_id' => 'género',
            'payroll_blood_type_id' => 'tipo de sangre',
            'country_id' => 'país',
            'estate_id' => 'estado',
            'municipality_id' => 'muncipio',
            'parish_id' => 'parroquia',
            'uniform_sizes' => 'talla de uniforme',
            'uniform_sizes.*.size' => 'talla de uniforme',
            'uniform_sizes.*.name' => 'nombre del uniforme',
            'medical_history' => 'historial médico',
            'social_security' => 'El campo seguro social',
            'passport' => ' El campo pasaporte',
        ];
    }

    /**
     * Muestra todos los registros de información personal del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::staffs.index');
    }

    /**
     * Muestra el formulario de registro de información personal del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::staffs.create-edit');
    }

    /**
     * Valida y registra una nueva información personal del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: result en verdadero y redirect con la url a donde ir
     */
    public function store(Request $request)
    {
        $parameter = Parameter::where([
            'active' => true, 'required_by' => 'payroll', 'p_key' => 'work_age',
        ])->first();
        $this->rules['id_number'] = ['required', 'regex:/^([\d]{7}|[\d]{8})$/u', 'unique:payroll_staffs,id_number'];
        $this->rules['passport'] = ['nullable', 'max:20', 'unique:payroll_staffs,passport'];
        $this->rules['birthdate'] = ['required', 'date', new AgeToWork(($parameter) ? $parameter->p_value : 0)];
        $this->validate($request, $this->rules, [
            'rif.regex' => 'El formato del campo rif es inválido. Debe estar formado por 10 caracteres, el primer carácter debe ser una letra: J,V,E,G o P (en mayúscula); los otros nueve carácteres deben ser números (X000000000).'
        ], $this->attributes);
        if ($request->has_disability) {
            $this->validate(
                $request,
                [
                    'payroll_disability_id' => ['required'],
                ],
                [],
                [
                    'payroll_disability_id' => 'discapacidad',
                ],
            );
        }
        if ($request->has_driver_license) {
            $this->validate(
                $request,
                [
                    'payroll_license_degree_id' => ['required'],
                ],
                [],
                [
                    'payroll_license_degree_id' => 'grado de licencia de conducir',
                ],
            );
        }

        $i = 0;
        foreach ($request->phones as $phone) {
            $this->validate(
                $request,
                [
                    'phones.' . $i . '.type' => ['required'],
                    'phones.' . $i . '.area_code' => ['required', 'digits:3'],
                    'phones.' . $i . '.number' => ['required', 'digits:7'],
                    'phones.' . $i . '.extension' => ['nullable', 'digits_between:3,6'],
                ],
                [],
                [
                    'phones.' . $i . '.type' => 'tipo #' . ($i + 1),
                    'phones.' . $i . '.area_code' => 'código de area #' . ($i + 1),
                    'phones.' . $i . '.number' => 'número #' . ($i + 1),
                    'phones.' . $i . '.extension' => 'extensión #' . ($i + 1),
                ]
            );
            $i++;
        }
        $codeSetting = CodeSetting::where('table', 'payroll_staffs')->first();
        if (!$codeSetting) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]);
            return response()->json(['result' => false, 'redirect' => route('payroll.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $payrollStaff = PayrollStaff::create([

            'code'  => generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                PayrollStaff::class,
                $codeSetting->field
            ),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'payroll_nationality_id' => $request->payroll_nationality_id,
            'id_number' => $request->id_number,
            'passport' => $request->passport,
            'rif' => $request->rif,
            'email' => $request->email,
            'birthdate' => $request->birthdate,
            'payroll_gender_id' => $request->payroll_gender_id,
            'has_disability' => ($request->has_disability) ? $request->has_disability : false,
            'payroll_disability_id' => ($request->has_disability) ? $request->payroll_disability_id : null,
            'has_driver_license' => ($request->has_driver_license) ? $request->has_driver_license : false,
            'payroll_license_degree_id' => ($request->has_driver_license) ? $request->payroll_license_degree_id : null,
            'social_security' => $request->social_security,
            'payroll_blood_type_id' => $request->payroll_blood_type_id,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
            'parish_id' => $request->parish_id,
            'address' => $request->address,
            'medical_history' => $request->medical_history,
        ]);

        if ($request->uniform_sizes && !empty($request->uniform_sizes)) {
            foreach ($request->uniform_sizes as $size) {
                $uniformSize = PayrollStaffUniformSize::create([
                    'name' => $size['name'],
                    'size' => $size['size'],
                    'payroll_staff_id' => $payrollStaff->id,
                ]);
            }
        }

        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $payrollStaff->phones()->save(new Phone([
                    'type' => $phone['type'],
                    'area_code' => $phone['area_code'],
                    'number' => $phone['number'],
                    'extension' => $phone['extension'],
                ]));
            }
        }
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('payroll.staffs.index')], 200);
    }

    /**
     * Muestra los datos de la información personal del trabajador en específico
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                          Identificador del dato a mostrar
     *
     * @return \Illuminate\Http\JsonResponse        Json con el dato de la información personal del trabajador
     */
    public function show($id)
    {
        $payrollStaff = PayrollStaff::where('id', $id)->with([
            'payrollNationality', 'payrollGender', 'payrollLicenseDegree', 'payrollBloodType', 'payrollDisability',
            'parish' => function ($query) {
                $query->with(['municipality' => function ($query) {
                    $query->with(['estate' => function ($query) {
                        $query->with('country');
                    }]);
                }]);
            }, 'phones',
        ])->first();
        return response()->json(['record' => $payrollStaff, 'age' => age($payrollStaff->birthdate)], 200);
    }

    /**
     * Muestra el formulario de actualización de información personal del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id              Identificador del dato a actualizar
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $payrollStaff = PayrollStaff::find($id);
        return view('payroll::staffs.create-edit', compact('payrollStaff'));
    }

    /**
     * Actualiza la información personal del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del dato a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con la redirección y mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $parameter = Parameter::where([
            'active' => true, 'required_by' => 'payroll', 'p_key' => 'work_age',
        ])->first();
        $payrollStaff = PayrollStaff::find($id);
        $this->rules['id_number'] = [
            'required', 'regex:/^([\d]{7}|[\d]{8})$/u', 'unique:payroll_staffs,id_number,' . $payrollStaff->id,
        ];
        $this->rules['passport'] = ['nullable', 'max:20', 'unique:payroll_staffs,passport,' . $payrollStaff->id];
        $this->rules['rif'] = [
            'required',
            'regex:/^[E, G, J, P, V, 0-9 ]+$/',
            'size:10',
            new RifRule(),
            Rule::unique('payroll_staffs', 'rif')->ignore($payrollStaff->id)
        ];
        $this->rules['email'] = ['nullable', 'unique:payroll_staffs,email,' . $payrollStaff->id, 'email'];
        $this->rules['birthdate'] = ['required', 'date', new AgeToWork(($parameter) ? $parameter->p_value : 0)];
        $this->validate($request, $this->rules, [], $this->attributes);
        if ($request->has_disability) {
            $this->validate(
                $request,
                [
                    'payroll_disability_id' => ['required'],
                ],
                [],
                [
                    'payroll_disability_id' => 'discapacidad',
                ],
            );
        }
        if ($request->has_driver_license) {
            $this->validate(
                $request,
                [
                    'payroll_license_degree_id' => ['required'],
                ],
                [],
                [
                    'payroll_license_degree_id' => 'grado de licencia de conducir',
                ],
            );
        }
        $i = 0;
        foreach ($request->phones as $phone) {
            $this->validate(
                $request,
                [
                    'phones.' . $i . '.type' => ['required'],
                    'phones.' . $i . '.area_code' => ['required', 'digits:3'],
                    'phones.' . $i . '.number' => ['required', 'digits:7'],
                    'phones.' . $i . '.extension' => ['nullable', 'digits_between:3,6'],
                ],
                [],
                [
                    'phones.' . $i . '.type' => 'tipo #' . ($i + 1),
                    'phones.' . $i . '.area_code' => 'código de area #' . ($i + 1),
                    'phones.' . $i . '.number' => 'número #' . ($i + 1),
                    'phones.' . $i . '.extension' => 'extensión #' . ($i + 1),
                ]
            );
            $i++;
        }
        $payrollStaff->first_name = $request->first_name;
        $payrollStaff->last_name = $request->last_name;
        $payrollStaff->payroll_nationality_id = $request->payroll_nationality_id;
        $payrollStaff->id_number = $request->id_number;
        $payrollStaff->passport = $request->passport;
        $payrollStaff->rif = $request->rif;
        $payrollStaff->email = $request->email;
        $payrollStaff->birthdate = $request->birthdate;
        $payrollStaff->payroll_gender_id = $request->payroll_gender_id;
        $payrollStaff->has_disability = ($request->has_disability) ? ($request->has_disability) : false;
        $payrollStaff->payroll_disability_id = ($request->has_disability) ? $request->payroll_disability_id : null;
        $payrollStaff->has_driver_license = ($request->has_driver_license) ? ($request->has_driver_license) : false;
        $payrollStaff->payroll_license_degree_id = ($request->has_driver_license) ?
            $request->payroll_license_degree_id : null;
        $payrollStaff->social_security = $request->social_security;
        $payrollStaff->payroll_blood_type_id = $request->payroll_blood_type_id;
        $payrollStaff->emergency_contact = $request->emergency_contact;
        $payrollStaff->emergency_phone = $request->emergency_phone;
        $payrollStaff->parish_id = $request->parish_id;
        $payrollStaff->address = $request->address;
        $payrollStaff->medical_history = $request->medical_history;
        $payrollStaff->save();

        foreach ($payrollStaff->payrollStaffUniformSize as $uniformSize) {
            $uniformSize->delete();
        }
        if ($payrollStaff->payrollStaffUniformSize == true) {
            foreach ($request->uniform_sizes as $size) {
                $uniformSize = PayrollStaffUniformSize::create([
                    'name' => $size['name'],
                    'size' => $size['size'],
                    'payroll_staff_id' => $payrollStaff->id,
                ]);
            }
        }
        foreach ($payrollStaff->phones as $phone) {
            $phone->delete();
        }
        if ($request->phones && !empty($request->phones)) {
            foreach ($request->phones as $phone) {
                $payrollStaff->phones()->updateOrCreate(
                    [
                        'type' => $phone['type'], 'area_code' => $phone['area_code'],
                        'number' => $phone['number'], 'extension' => $phone['extension'],
                    ],
                    [
                        'type' => $phone['type'], 'area_code' => $phone['area_code'],
                        'number' => $phone['number'], 'extension' => $phone['extension'],
                    ]
                );
            }
        }
        //actualizando el perfil tambien si lo tiene
        $staffEmployment = PayrollEmployment::with('profile')->where('payroll_staff_id', $id)->first();
        if ($staffEmployment) {
            $staffEmployment->Profile()->Update([
                'first_name' => $payrollStaff->first_name,
                'last_name' => $payrollStaff->last_name,
            ]);
        }
        //actualizando el perfil tambien si lo tiene
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('payroll.staffs.index')], 200);
    }

    /**
     * Elimina la información personal del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del dato a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollStaff = PayrollStaff::find($id);
        $payrollStaffUniformSize = PayrollStaffUniformSize::where('payroll_staff_id', $id);
        /*Eliminar las relaciones de expediente*/
        $payrollEmployment = PayrollEmployment::where('payroll_staff_id', $id);
        $payrollProfessional = PayrollProfessional::where('payroll_staff_id', $id);
        $payrollSocioeconomic = PayrollSocioeconomic::where('payroll_staff_id', $id);
        $payrollFinancial = PayrollFinancial::where('payroll_staff_id', $id);

        $payrollStaffUniformSize->delete();
        $payrollEmployment->delete();
        $payrollProfessional->delete();
        $payrollSocioeconomic->delete();
        $payrollFinancial->delete();
        $payrollStaff->delete();
        return response()->json(['record' => $payrollStaff, 'message' => 'Registro eliminado con exito.'], 200);
    }

    /**
     * Muestra la información laboral personal del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la información personal del trabajador
     */
    public function vueList(Request $request)
    {
        $records = PayrollStaff::without(
            'payrollFinancial',
            'payrollEmployment',
            'payrollSocioeconomic',
            'payrollProfessional'
        )->with([
            'payrollNationality',
            'payrollGender' => function ($query) {
                $query->select('id', 'name');
            },
            'parish',
            'payrollLicenseDegree' => function ($query) {
                $query->select('id', 'name', 'description');
            },
            'payrollBloodType' => function ($query) {
                $query->select('id', 'name');
            },
            'payrollDisability' => function ($query) {
                $query->select('id', 'name', 'description');
            },
            'phones',
            'payrollStaffUniformSize' => function ($query) {
                $query->select('id', 'name', 'size', 'payroll_staff_id');
            },
        ])
        ->search($request->get('query'))
        ->paginate($request->get('limit'));

        return response()->json(
            [
                'data' => $records->items(),
                'count' => $records->total(),
            ],
            200
        );
    }

    /**
     * Obtiene la información personal de los trabajadores
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la información personal de los trabajadores
     */
    public function getPayrollStaffs($type = 'all')
    {
        if ($type === 'all') {
            return response()->json(
                PayrollStaff::query()
                    ->select('id', 'id_number', 'first_name', 'last_name')
                    ->toBase()
                    ->get()
                    ->map(function ($staff) {
                        return [
                            'id' => $staff->id,
                            'text' => $staff->id_number . ' - ' . $staff->first_name . ' ' . $staff->last_name,
                        ];
                    })->prepend([
                        'id' => '',
                        'text' => 'Seleccione...'
                    ])
            );
        } elseif ($type === 'all-active') {
            return response()->json(
                PayrollStaff::query()
                    ->select('id', 'id_number', 'first_name', 'last_name')
                    ->whereHas('payrollEmployment', function ($query) {
                        $query->where('active', true);
                    })
                    ->get()
                    ->map(function ($staff) {
                        return [
                            'id' => $staff->id,
                            'text' => $staff->id_number
                                . ' - '
                                . $staff->first_name
                                . ' '
                                . $staff->last_name,
                        ];
                    })
                    ->prepend([
                        'id' => '',
                        'text' => 'Seleccione...'
                    ])
            );
        } elseif (is_numeric($type)) {
            $options = [['id' => '', 'text' => 'Seleccione...']];

            /* Filtra por el personal que aún no tiene registrado los datos Socioeconomico */
            $staffs = PayrollStaff::doesnthave('payrollEmployment')->get();
            foreach ($staffs as $staff) {
                $options[] = [
                    'id' => $staff->id,
                    'text' => "{$staff->id_number} - {$staff->full_name}"
                ];
            }
            $editStaff = PayrollEmployment::with('payrollStaff')->where('id', (int) $type)->first();

            if ($editStaff) {
                $pushOptions = [
                    'id' => $editStaff->payrollStaff->id,
                    'text' => "{$editStaff->payrollStaff->id_number} - {$editStaff->payrollStaff->full_name}",
                    'employee_id' => $editStaff->id
                ];

                array_push($options, $pushOptions);
            }

            return response()->json($options);
        } elseif ($type == 'auth') {
            if (auth()->user()->hasRole('admin, payroll')) {
                return response()->json(
                    template_choices(
                        PayrollStaff::class,
                        ['id_number', '-', 'full_name'],
                        ['relationship' => 'payrollEmployment', 'where' => ['active' => true]],
                        true
                    )
                );
            } else {
                $profile = Profile::where('user_id', auth()->user()->id)->first();
                $options = [['id' => '', 'text' => 'Seleccione...']];
                $staff = PayrollEmployment::with('payrollStaff')->where('id', $profile->employee_id)->first();
                if ($staff) {
                    $pushOptions = ['id' => $staff->payrollStaff->id, 'text' => "{$staff->payrollStaff->id_number} - {$staff->payrollStaff->full_name}"];
                    array_push($options, $pushOptions);
                }

                return response()->json($options);
            }
        } elseif ($type == 'financial') {
            $options = [['id' => '', 'text' => 'Seleccione...']];
            /* Filtra por el personal que aún no tiene registrado los datos financieros */
            $staffs = PayrollStaff::doesnthave('payrollFinancial')->get();

            foreach ($staffs as $staff) {
                $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
            }
            return response()->json($options);
        } elseif ($type == 'staff-accounts') {
            $options = [['id' => '', 'text' => 'Seleccione...']];
            /* Filtra por el personal que aún no tiene registrado cuentas contables */
            $staffs = PayrollStaff::query()
                ->doesnthave('payrollStaffAccount')
                ->when(!empty(request()->payroll_staff_id), function ($query) {
                    return $query->orWhere('id', request()->payroll_staff_id);
                })
                ->get();

            foreach ($staffs as $staff) {
                $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
            }
            return response()->json($options);
        }

        $options = [['id' => '', 'text' => 'Seleccione...']];
        /* Filtra por el personal que aún no tiene registrado los datos laborales */
        $staffs = PayrollStaff::doesnthave('payrollEmployment')->get();

        foreach ($staffs as $staff) {
            $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
        }

        return response()->json($options);
    }

    /**
     * Obtiene la información personal de los trabajadores
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la información personal de los trabajadores
     */
    public function getPayrollSocioeconomic($type = 'all')
    {
        if ($type === 'all') {
            return response()->json(template_choices(PayrollStaff::class, ['id_number', '-', 'full_name'], '', true));
        } elseif (is_numeric($type)) {
            $options = [['id' => '', 'text' => 'Seleccione...']];

            /** Filtra por el personal que aún no tiene registrado los datos Socioeconomico */
            $staffs = PayrollStaff::doesnthave('payrollSocioeconomic')->get();
            foreach ($staffs as $staff) {
                $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
            }
            $editStaff = PayrollSocioeconomic::with('payrollStaff')->where('id', (int) $type)->get();

            $pushOptions = ['id' => $editStaff[0]->payrollStaff->id, 'text' => "{$editStaff[0]->payrollStaff->id_number} - {$editStaff[0]->payrollStaff->full_name}"];

            array_push($options, $pushOptions);

            return response()->json($options);
        }

        $options = [['id' => '', 'text' => 'Seleccione...']];

        /* Filtra por el personal que aún no tiene registrado los datos Socioeconomico */
        $staffs = PayrollStaff::doesnthave('payrollSocioeconomic')->get();
        foreach ($staffs as $staff) {
            $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
        }
        return response()->json($options);
    }

    /**
     * Realiza la acción necesaria para importar los datos Personales
     *
     * @author    Francisco Escala
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
     * Exporta la información de datos personales de los trabajadores
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        $userId = auth()->user()->id;
        PayrollExportNotification::dispatch(
            $userId,
            'Datos Personales',
        );

        request()->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
                'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.staffs.index');
    }

    /**
     * Obtiene la información personal de los trabajadores
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la información personal de los trabajadores
     */
    public function getPayrollProfessional($type = 'all')
    {
        if ($type === 'all') {
            return response()->json(template_choices(PayrollStaff::class, ['id_number', '-', 'full_name'], '', true));
        } elseif (is_numeric($type)) {
            $options = [['id' => '', 'text' => 'Seleccione...']];

            /* Filtra por el personal que aún no tiene registrado los datos Socioeconomico */
            $staffs = PayrollStaff::doesnthave('payrollProfessional')->get();
            foreach ($staffs as $staff) {
                $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
            }
            $editStaff = PayrollProfessional::with('payrollStaff')->where('id', (int) $type)->get();

            $pushOptions = ['id' => $editStaff[0]->payrollStaff->id, 'text' => "{$editStaff[0]->payrollStaff->id_number} - {$editStaff[0]->payrollStaff->full_name}"];

            array_push($options, $pushOptions);

            return response()->json($options);
        }

        $options = [['id' => '', 'text' => 'Seleccione...']];

        /* Filtra por el personal que aún no tiene registrado los datos Socioeconomico */
        $staffs = PayrollStaff::doesnthave('payrollProfessional')->get();
        foreach ($staffs as $staff) {
            $options[] = ['id' => $staff->id, 'text' => "{$staff->id_number} - {$staff->full_name}"];
        }
        return response()->json($options);
    }
}
