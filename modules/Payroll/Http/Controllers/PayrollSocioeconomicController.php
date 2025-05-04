<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollFamilyBurden;
use Modules\Payroll\Models\PayrollSocioeconomic;
use Modules\Payroll\Jobs\PayrollExportNotification;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class PayrollSocioeconomicController
 * @brief Controlador de información socioeconómica del trabajador
 *
 * Clase que gestiona los datos de información socioeconómica del trabajador
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSocioeconomicController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

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
        $this->middleware('permission:payroll.socioeconomics.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.socioeconomics.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.socioeconomics.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.socioeconomics.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.socioeconomics.import', ['only' => 'import']);
        $this->middleware('permission:payroll.socioeconomics.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->rules = [
            'marital_status_id' => ['required'],
            'payroll_staff_id' => ['required', 'unique:payroll_socioeconomics,payroll_staff_id'],
        ];

        $this->messages = [
            'marital_status_id.required' => 'El campo estado civil es obligatorio',
            'payroll_staff_id.required' => 'El campo trabajador es obligatorio'
        ];
    }

    /**
     * Muestra todos los registros de información socioeconómica del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::socioeconomics.index');
    }

    /**
     * Muestra el formulario de registro de información socioeconómica del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::socioeconomics.create-edit');
    }

    /**
     * Valida y registra nueva información socioeconómica del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: result en verdadero y redirect con la url a donde ir
     */
    public function store(Request $request)
    {
        $rules = $this->rules;
        $messages = $this->messages;

        $relationshipSonId = \Modules\Payroll\Models\PayrollRelationship::where('name', 'Hijo(a)')->value('id');
        $request->validate([
            'payroll_childrens' => [
                'array',
                function ($attribute, $value, $fail) use ($relationshipSonId) {
                    $relationshipIds = collect($value)
                        ->pluck('payroll_relationships_id')
                        ->reject(function ($id) use ($relationshipSonId) {
                            return $id == $relationshipSonId;
                        });

                    if ($relationshipIds->count() > $relationshipIds->unique()->count()) {
                        $fail('la relacion con el pariente no puede repetirse a menos que sea hijos.');
                    }
                },
            ],
            'payroll_childrens.*.payroll_relationships_id' => 'required|integer',
            // Add other validation rules for the nested fields as needed
        ], [
            'payroll_childrens.*.payroll_relationships_id.required' => 'La información del pariente es obligatoria.',
        ]);
        foreach ($request->payroll_childrens ?? [] as $i => $payrollChildren) {
            $rules = array_merge($rules, [
                'payroll_childrens.' . $i . '.payroll_relationships_id' => ['required'],
                'payroll_childrens.' . $i . '.first_name' => ['required'],
                'payroll_childrens.' . $i . '.last_name' => ['required'],
                'payroll_childrens.' . $i . '.birthdate' => ['required', 'date'],
                'payroll_childrens.' . $i . '.id_number' => [
                    'sometimes',
                    'nullable',
                    Rule::requiredIf($payrollChildren["age"] > 11),
                    'unique:payroll_family_burdens,id_number',
                    'regex:/^([\d]{7}|[\d]{8})$/u',
                ],
                'payroll_childrens.' . $i . '.payroll_gender_id' => ['required'],
                'payroll_childrens.' . $i . '.payroll_schooling_level_id'
                => [Rule::requiredIf($payrollChildren["is_student"] === true)],
                'payroll_childrens.' . $i . '.study_center'
                => [Rule::requiredIf($payrollChildren["is_student"] === true)],
                'payroll_childrens.' . $i . '.payroll_scholarship_types_id'
                => [Rule::requiredIf($payrollChildren["has_scholarship"] ?? false === true)],
                'payroll_childrens.' . $i . '.payroll_disability_id'
                => [Rule::requiredIf($payrollChildren["has_disability"] === true)],
            ]);
            $messages = array_merge($messages, [
                'payroll_childrens.' . $i . '.payroll_relationships_id.required'
                => 'El campo parentesco del pariente #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.first_name.required'
                => 'El campo nombres del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.last_name.required'
                => 'El campo apellidos del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.birthdate.required'
                => 'El campo fecha de nacimiento del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.id_number.required'
                => 'El campo cédula de identidad del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.id_number.regex'
                => 'El campo cédula de identidad del pariente  #' . ($i + 1) . ' es inválido',
                'payroll_childrens.' . $i . '.id_number.unique'
                => 'El campo cédula de identidad del pariente  #' . ($i + 1) . ' ya existe en el registro',
                'payroll_childrens.' . $i . '.payroll_gender_id.required'
                => 'El campo género del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.payroll_schooling_level_id.required'
                => 'El campo nivel de escolaridad #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.study_center.required'
                => 'El campo centro de estudio #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.payroll_scholarship_types_id.required'
                => 'El campo tipo de beca #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.payroll_disability_id.required'
                => 'El campo discapacidad #' . ($i + 1) . ' es obligatorio',
            ]);
        }
        $this->validate($request, $rules, $messages);
        DB::transaction(function () use ($request) {
            $payrollSocioeconomic = PayrollSocioeconomic::create([
                'payroll_staff_id' => $request->payroll_staff_id,
                'marital_status_id' => $request->marital_status_id,
            ]);
            if ($request->payroll_childrens && !empty($request->payroll_childrens)) {
                foreach ($request->payroll_childrens as $payrollChildren) {
                    PayrollFamilyBurden::create([
                        'first_name' => $payrollChildren['first_name'],
                        'last_name' => $payrollChildren['last_name'],
                        'id_number' => $payrollChildren['id_number'],
                        'birthdate' => $payrollChildren['birthdate'],
                        'age' => $payrollChildren['age'],
                        'address' => array_key_exists('address', $payrollChildren) ? $payrollChildren['address'] : "",
                        'payroll_gender_id' => $payrollChildren['payroll_gender_id'],
                        'is_student' => $payrollChildren['is_student'],
                        'has_disability' => $payrollChildren['has_disability'],
                        'has_scholarships' => $payrollChildren['has_scholarships'],
                        'study_center' => $payrollChildren['study_center'],
                        'payroll_schooling_level_id' => $payrollChildren['payroll_schooling_level_id'],
                        'payroll_relationships_id' => $payrollChildren['payroll_relationships_id'],
                        'payroll_scholarship_types_id' => $payrollChildren['payroll_scholarship_types_id'],
                        'payroll_disability_id' => $payrollChildren['payroll_disability_id'],
                        'payroll_socioeconomic_id' => $payrollSocioeconomic->id,
                    ]);
                }
            }
        });
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json([
            'result' => true, 'redirect' => route('payroll.socioeconomics.index'),
        ], 200);
    }

    /**
     * Muestra los datos de información socioeconómica del trabajador en específico
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                          Identificador del dato a mostrar
     *
     * @return \Illuminate\Http\JsonResponse        Json con el dato de información socioeconómica del trabajador
     */
    public function show($id)
    {
        $payrollSocioeconomic = PayrollSocioeconomic::where('id', $id)->with([
            'payrollStaff', 'maritalStatus', 'payrollChildrens' => function ($query) {
                $query->with([
                    'payrollSchoolingLevel',
                    'payrollDisability',
                    'payrollScholarshipType',
                    'payrollRelationship',
                ]);
            },
        ])->first();

        return response()->json(['record' => $payrollSocioeconomic], 200);
    }

    /**
     * Muestra el formulario de actualización de información socioeconómica del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id              Identificador con el dato a actualizar
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $payrollSocioeconomic = PayrollSocioeconomic::find($id);
        return view('payroll::socioeconomics.create-edit', compact('payrollSocioeconomic'));
    }

    /**
     * Actualiza la información socioeconómica del trabajador
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
        $payrollSocioeconomic = PayrollSocioeconomic::with(['payrollChildrens'])->find($id);
        $this->rules['payroll_staff_id'] = [
            'required', 'unique:payroll_socioeconomics,payroll_staff_id,' . $payrollSocioeconomic->id,
        ];

        $rules = $this->rules;
        $messages = $this->messages;

        $relationshipSonId = \Modules\Payroll\Models\PayrollRelationship::where('name', 'Hijo(a)')->value('id');
        $request->validate(
            [
                'payroll_childrens' => [
                    'array',
                    function ($attribute, $value, $fail) use ($relationshipSonId) {
                        $relationshipIds = collect($value)
                            ->pluck('payroll_relationships_id')
                            ->reject(function ($id) use ($relationshipSonId) {
                                return $id == $relationshipSonId;
                            });

                        if ($relationshipIds->count() > $relationshipIds->unique()->count()) {
                            $fail('la relacion con el pariente no puede repetirse a menos que sea hijos.');
                        }
                    },
                ],
                'payroll_childrens.*.payroll_relationships_id' => 'required|integer',
            ],
            [
                'payroll_childrens.*.payroll_relationships_id.required' => 'La información del pariente es obligatoria.',
            ]
        );

        foreach ($request->payroll_childrens ?? [] as $i => $payrollChildren) {
            $rules = array_merge($rules, [
                'payroll_childrens.' . $i . '.payroll_relationships_id' => ['required'],
                'payroll_childrens.' . $i . '.first_name' => ['required'],
                'payroll_childrens.' . $i . '.last_name' => ['required'],
                'payroll_childrens.' . $i . '.birthdate' => ['required', 'date'],
                'payroll_childrens.' . $i . '.id_number' => [
                    'sometimes',
                    'nullable',
                    Rule::requiredIf($payrollChildren["age"] > 11),
                    'regex:/^([\d]{7}|[\d]{8})$/u'
                ],
                'payroll_childrens.' . $i . '.payroll_gender_id' => ['required'],
                'payroll_childrens.' . $i . '.payroll_schooling_level_id'
                => [Rule::requiredIf($payrollChildren["is_student"] === true)],
                'payroll_childrens.' . $i . '.study_center'
                => [Rule::requiredIf($payrollChildren["is_student"] === true)],
                'payroll_childrens.' . $i . '.payroll_scholarship_types_id'
                => [Rule::requiredIf($payrollChildren["has_scholarship"] ?? false === true)],
                'payroll_childrens.' . $i . '.payroll_disability_id'
                => [Rule::requiredIf($payrollChildren["has_disability"] === true)],
            ]);

            if (isset($payrollChildren["id"])) {
                array_push(
                    $rules['payroll_childrens.' . $i . '.id_number'],
                    Rule::unique('payroll_family_burdens', 'id_number')->withoutTrashed()->ignore($payrollChildren["id"])
                );
            } else {
                array_push(
                    $rules['payroll_childrens.' . $i . '.id_number'],
                    Rule::unique('payroll_family_burdens', 'id_number')
                );
            }

            $messages = array_merge($messages, [
                'payroll_childrens.' . $i . '.payroll_relationships_id.required'
                => 'El campo parentesco del pariente #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.first_name.required'
                => 'El campo nombres del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.last_name.required'
                => 'El campo apellidos del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.birthdate.required'
                => 'El campo fecha de nacimiento del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.id_number.required'
                => 'El campo cédula de identidad del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.id_number.regex'
                => 'El campo cédula de identidad del pariente  #' . ($i + 1) . ' es inválido',
                'payroll_childrens.' . $i . '.id_number.unique'
                => 'El campo cédula de identidad del pariente  #' . ($i + 1) . ' ya existe en el registro',
                'payroll_childrens.' . $i . '.payroll_gender_id.required'
                => 'El campo género del pariente  #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.payroll_schooling_level_id.required'
                => 'El campo nivel de escolaridad #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.study_center.required'
                => 'El campo centro de estudio #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.payroll_scholarship_types_id.required'
                => 'El campo tipo de beca #' . ($i + 1) . ' es obligatorio',
                'payroll_childrens.' . $i . '.payroll_disability_id.required'
                => 'El campo discapacidad #' . ($i + 1) . ' es obligatorio',
            ]);
        }
        $this->validate($request, $rules, $messages);

        $payrollSocioeconomic->payroll_staff_id = $request->payroll_staff_id;
        $payrollSocioeconomic->marital_status_id = $request->marital_status_id;
        $payrollSocioeconomic->save();
        if (count($request->payroll_childrens) != count($payrollSocioeconomic->payrollChildrens)) {
            foreach ($payrollSocioeconomic->payrollChildrens as $payrollChildren) {
                $payrollChildren->delete();
            }
        }

        if ($request->payroll_childrens && !empty($request->payroll_childrens)) {
            foreach ($request->payroll_childrens as $payrollChildren) {
                if (!is_null($payrollChildren["id_number"]) && !empty($payrollChildren["id_number"])) {
                    $indentifier =                  [
                        'first_name' => $payrollChildren['first_name'],
                        'id_number' => $payrollChildren['id_number'],
                        'last_name' => $payrollChildren['last_name'],
                    ];
                } else {
                    $indentifier =
                        [
                            'first_name' => $payrollChildren['first_name'],
                            'last_name' => $payrollChildren['last_name'],
                        ];
                }
                PayrollFamilyBurden::withTrashed()->updateOrCreate(
                    $indentifier,
                    [
                        'first_name' => $payrollChildren['first_name'],
                        'last_name' => $payrollChildren['last_name'],
                        'id_number' => $payrollChildren['id_number'],
                        'birthdate' => $payrollChildren['birthdate'],
                        'age' => $payrollChildren['age'],
                        'address' => array_key_exists('address', $payrollChildren) ? $payrollChildren['address'] : "",
                        'payroll_gender_id' => $payrollChildren['payroll_gender_id'],
                        'is_student' => $payrollChildren['is_student'],
                        'has_disability' => $payrollChildren['has_disability'],
                        'has_scholarships' => $payrollChildren['has_scholarships'],
                        'study_center' => $payrollChildren['study_center'],
                        'payroll_schooling_level_id' => $payrollChildren['payroll_schooling_level_id'],
                        'payroll_relationships_id' => $payrollChildren['payroll_relationships_id'],
                        'payroll_scholarship_types_id' => $payrollChildren['payroll_scholarship_types_id'],
                        'payroll_disability_id' => $payrollChildren['payroll_disability_id'],
                        'payroll_socioeconomic_id' => $payrollSocioeconomic->id,
                        'deleted_at' => null,
                    ]
                );
            }
        } else {
            foreach ($payrollSocioeconomic->payrollChildrens as $payrollChildren) {
                $payrollChildren->delete();
            }
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json([
            'result' => true, 'redirect' => route('payroll.socioeconomics.index'),
        ], 200);
    }

    /**
     * Realiza la acción necesaria para importar los datos Socioeconómicos
     *
     * @author    Francisco Escala
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        $userId = auth()->user()->id;
        PayrollExportNotification::dispatch(
            $userId,
            'Datos Socioeconomicos',
        );

        request()->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
                'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.socioeconomics.index');
    }

    /**
     * Elimina la información socioeconómica del trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del dato a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $payrollSocioeconomic = PayrollSocioeconomic::find($id);
        $payrollSocioeconomic->delete();

        $payrollChildrens = PayrollFamilyBurden::where('payroll_socioeconomic_id', $payrollSocioeconomic->id)->get();
        foreach ($payrollChildrens as $payrollChildren) {
            $payrollChildren->delete();
        }

        return response()->json(['record' => $payrollSocioeconomic, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información socioeconómica del trabajador registrada
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la información socioeconómica del trabajador
     */
    public function vueList(Request $request)
    {
        $records = PayrollSocioeconomic::with([
            'payrollStaff' => function ($query) {
                $query->without(
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollGender',
                    'payrollBloodType',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollEmployment',
                    'payrollStaffUniformSize',
                    'payrollSocioeconomic',
                    'payrollProfessional'
                );
            },
            'maritalStatus',
            'payrollChildrens' => function ($query) {
                $query->with([
                    'payrollSchoolingLevel',
                    'payrollDisability',
                    'payrollScholarshipType',
                    'payrollRelationship',
                    'payrollGender',
                ]);
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
}
