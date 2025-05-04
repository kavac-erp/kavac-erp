<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Department;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollGuardScheme;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollSupervisedGroup;
use Modules\Payroll\Models\PayrollSupervisedGroupStaff;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Models\PayrollTimeSheetPending;

/**
 * @class PayrollSupervisedGroupController
 * @brief Controlador de grupos supervisados
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSupervisedGroupController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.supervisedgroup.index', ['only' => 'index']);
        $this->middleware('permission:payroll.supervisedgroup.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.supervisedgroup.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.supervisedgroup.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'code' => ['required', 'unique:payroll_supervised_groups,code'],
            'supervisor_id' => ['required'],
            'approver_id' => ['required'],
            'supervised' => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'code.required'        => 'El campo código es obligatorio.',
            'code.unique'          => 'El campo código ya ha sido registrado.',
            'supervisor_id.required' => 'El campo supervisor es obligatorio.',
            'approver_id.required' => 'El campo aprobador es obligatorio.',
            'supervised.required' => 'El campo supervisados es obligatorio.',
        ];
    }

    /**
     * Obtiene todos los registros de grupos supervisados
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos de los grupos supervisados
     */
    public function index()
    {
        return response()->json(['records' => PayrollSupervisedGroup::query()
            ->with('supervisor', 'approver', 'payrollSupervisedGroupStaff.payrollStaff')
            ->get()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de grupo supervisado
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * [descripción del método]
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollSupervisedGroup = DB::transaction(function () use ($request) {
            $payrollSupervisedGroup = PayrollSupervisedGroup::create([
                'code' => $request->code,
                'supervisor_id' => $request->supervisor_id,
                'approver_id' => $request->approver_id
            ]);

            foreach ($request->supervised as $value) {
                PayrollSupervisedGroupStaff::create([
                    'payroll_supervised_group_id' => $payrollSupervisedGroup->id,
                    'payroll_staff_id' => $value['id']
                ]);
            }
            return $payrollSupervisedGroup;
        });
        return response()->json(['record' => $payrollSupervisedGroup, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un grupo supervisado
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar la información de un grupo supervisado
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información de un grupo supervisado
     *
     * @param     Request    $request  Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollSupervisedGroup = PayrollSupervisedGroup::find($id);
        $this->validateRules['code'] = [
            'required',
            'unique:payroll_supervised_groups,code,' . $payrollSupervisedGroup->id
        ];
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollSupervisedGroup) {
            $payrollSupervisedGroup->code = $request->code;
            $payrollSupervisedGroup->supervisor_id = $request->supervisor_id;
            $payrollSupervisedGroup->approver_id = $request->approver_id;
            $payrollSupervisedGroup->save();

            PayrollSupervisedGroupStaff::query()
                ->where('payroll_supervised_group_id', $payrollSupervisedGroup->id)
                ->delete();

            foreach ($request->supervised as $value) {
                PayrollSupervisedGroupStaff::create([
                    'payroll_supervised_group_id' => $payrollSupervisedGroup->id,
                    'payroll_staff_id' => $value['id']
                ]);
            }
        });
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un grupo supervisado
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $timeSheet = PayrollTimeSheet::query()
            ->where('payroll_supervised_group_id', $id)
            ->first();

        $timeSheetPending = PayrollTimeSheetPending::query()
            ->where('payroll_supervised_group_id', $id)
            ->first();

        $guardScheme = PayrollGuardScheme::query()
            ->where('payroll_supervised_group_id', $id)
            ->first();

        if ($timeSheet || $timeSheetPending || $guardScheme) {
            return response()->json(['error' => true, 'message' => __('No se puede eliminar el grupo de supervisados' .
                ' debido a que tiene una hoja de tiempo asociada')], 200);
        }

        $payrollSupervisedGroup = PayrollSupervisedGroup::find($id);

        $groupStaff = PayrollSupervisedGroupStaff::query()
            ->where('payroll_supervised_group_id', $payrollSupervisedGroup->id)
            ->get();

        foreach ($groupStaff as $staff) {
            $staff->delete();
        }

        $payrollSupervisedGroup->delete();

        return response()->json(['record' => $payrollSupervisedGroup, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el personal asignado a un grupo supervisado
     *
     * @param string $ids Identificadores del personal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupedStaff($ids = '')
    {
        $ids = explode(',', $ids);

        $registeredSupervisedIds = [];

        $registeredSupervised = PayrollSupervisedGroupStaff::query()
            ->toBase()
            ->get();

        foreach ($registeredSupervised as $supervised) {
            $registeredSupervisedIds[$supervised->payroll_staff_id] = $supervised->payroll_staff_id;
        }

        if (count($ids) > 0) {
            foreach ($ids as $id) {
                unset($registeredSupervisedIds[$id]);
            }
        }

        $staffs = PayrollStaff::query()
            ->has('payrollEmployment')
            ->whereNotIn('id', $registeredSupervisedIds)
            ->toBase()
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'text' => ($staff->id_number ?? $staff->passport) .
                        ' - ' . $staff->first_name . ' ' . $staff->last_name,
                    'payrollEmployment' => PayrollEmployment::query()
                        ->where('payroll_staff_id', $staff->id)
                        ->where('active', true)
                        ->toBase()
                        ->get()
                        ->map(function ($employment) {
                            return [
                                'department' => Department::query()
                                    ->toBase()
                                    ->find($employment->department_id)
                            ];
                        })
                ];
            });

        $data = [];

        foreach ($staffs as $staff) {
            if (count($staff['payrollEmployment']) > 0) {
                if (!array_key_exists($staff['payrollEmployment'][0]['department']->name, $data)) {
                    $data[$staff['payrollEmployment'][0]['department']->name] =
                    [
                        'label' => $staff['payrollEmployment'][0]['department']->name,

                        'group' => [
                            0 => [
                                'id' => $staff['id'],
                                'text' => $staff['text'],
                                'group' => $staff['payrollEmployment'][0]['department']->name,
                            ]
                        ]
                    ];
                } else {
                    $data[$staff['payrollEmployment'][0]['department']->name]['group'][] =
                    [
                        'id' => $staff['id'],
                        'text' => $staff['text'],
                        'group' => $staff['payrollEmployment'][0]['department']->name,
                    ];
                }
            }
        }

        return response()->json($data);
    }

    /**
     * Obtiene los datos de grupos supervisados
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayrollSupervisedGroups(Request $request)
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        $staffs = [];
        $timeSheet = null;

        if ($request->type == 'active') {
            $timeSheet = PayrollTimeSheet::with(['payrollSupervisedGroup'])->find($request->id);
        } elseif ($request->type == 'pending') {
            $timeSheet = PayrollTimeSheetPending::with(['payrollSupervisedGroup'])->find($request->id);
        } elseif ($request->type == 'scheme') {
            $guardScheme = PayrollGuardScheme::with(['payrollSupervisedGroup'])->find($request->id);

            $staffIds = [];

            foreach ($guardScheme->data_source as $key => $items) {
                foreach ($items as $item) {
                    if ($item['count'] > 0) {
                        preg_match('/(\d+)-/', $key, $matches);

                        if (isset($matches[1])) {
                            $staffIds[] = (int)$matches[1];
                        }
                    }
                }
            }

            $staffs = PayrollStaff::query()
                ->whereIn('id', $staffIds)
                ->get()
                ->map(function ($staff) {
                    return [
                        'id' => $staff->id,
                        'id_number' => $staff->id_number,
                        'name' => $staff->fullName,
                        'worksheet_code' => $staff->payrollEmployment?->worksheet_code ?? '',
                    ];
                });
        }

        $data = $timeSheet ? $timeSheet->time_sheet_data : null;

        if ($data) {
            $staffIds = array_map(function ($key) use ($data) {
                $number = (int)explode("-", $key)[1];
                $value = $data[$key];
                return ($value > 0) ? $number : null;
            }, array_filter(array_keys($data), function ($key) {
                return strpos($key, "total-") === 0;
            }));

            // Filtrar los números no nulos (mayores que 0)
            $staffIds = array_filter($staffIds, function ($number) {
                return $number !== null;
            });

            $staffs = PayrollStaff::query()
                ->whereIn('id', $staffIds)
                ->get()
                ->map(function ($staff) {
                    return [
                        'id' => $staff->id,
                        'id_number' => $staff->id_number,
                        'name' => $staff->fullName,
                        'worksheet_code' => $staff->payrollEmployment?->worksheet_code ?? '',
                    ];
                });
        }

        if ($user->hasRole('admin, payroll')) {
            $records = PayrollSupervisedGroup::query()
                ->select('id', 'code', 'supervisor_id', 'approver_id')
                ->with(['supervisor', 'approver', 'payrollSupervisedGroupStaff.payrollStaff'])
                ->get()
                ->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'text' => $group->code,
                        'supervisor_id' => $group->supervisor_id,
                        'supervisor' => $group->supervisor ? [
                            'name' => ($group->supervisor->id_number ?? $group->supervisor->passport) . ' - ' .
                                $group->supervisor->first_name . ' ' . $group->supervisor->last_name,
                            'department' => $group->supervisor->payrollEmployment?->department?->name ?? '',
                        ] : null,
                        'approver_id' => $group->approver_id,
                        'approver' => $group->approver ? [
                            'name' => ($group->approver->id_number ?? $group->approver->passport) . ' - ' .
                                $group->approver->first_name . ' ' . $group->approver->last_name,
                            'department' => $group->approver->payrollEmployment?->department?->name ?? '',
                        ] : null,
                        'payroll_staffs' => $group->payrollSupervisedGroupStaff
                            ->sortBy(function ($staff) {
                                return $staff->payrollStaff->first_name;
                            })
                            ->map(function ($staff) {
                                return [
                                    'id' => $staff->payrollStaff->id,
                                    'id_number' => $staff->payrollStaff->id_number,
                                    'name' => $staff->payrollStaff->fullName,
                                    'worksheet_code' => $staff->payrollStaff->payrollEmployment?->worksheet_code ?? '',
                                ];
                            })
                            ->values()
                            ->toArray(),
                        'last_date_guard_scheme' => $group->payrollGuardSchemes()->orderBy('to_date', 'desc')->first()?->to_date ?? '',
                    ];
                })
                ->toArray();
        } else {
            $records = PayrollSupervisedGroup::query()
                ->select('id', 'code', 'supervisor_id', 'approver_id')
                ->with(['supervisor', 'approver', 'payrollSupervisedGroupStaff.payrollStaff'])
                ->where('supervisor_id', $profileUser->employee_id)
                ->orWhere('approver_id', $profileUser->employee_id)
                ->get()
                ->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'text' => $group->code,
                        'supervisor_id' => $group->supervisor_id,
                        'supervisor' => $group->supervisor ? [
                            'name' => ($group->supervisor->id_number ?? $group->supervisor->passport) . ' - ' .
                                $group->supervisor->first_name . ' ' . $group->supervisor->last_name,
                            'department' => $group->supervisor->payrollEmployment?->department?->name ?? '',
                        ] : null,
                        'approver_id' => $group->approver_id,
                        'approver' => $group->approver ? [
                            'name' => ($group->approver->id_number ?? $group->approver->passport) . ' - ' .
                                $group->approver->first_name . ' ' . $group->approver->last_name,
                            'department' => $group->approver->payrollEmployment?->department?->name ?? '',
                        ] : null,
                        'payroll_staffs' => $group->payrollSupervisedGroupStaff
                            ->sortBy(function ($staff) {
                                return $staff->payrollStaff->first_name;
                            })
                            ->map(function ($staff) {
                                return [
                                    'id' => $staff->payrollStaff->id,
                                    'id_number' => $staff->payrollStaff->id_number,
                                    'name' => $staff->payrollStaff->fullName,
                                    'worksheet_code' => $staff->payrollStaff->payrollEmployment?->worksheet_code ?? '',
                                ];
                            })
                            ->values()
                            ->toArray(),
                        'last_date_guard_scheme' => $group->payrollGuardSchemes()->orderBy('to_date', 'desc')->first()?->to_date ?? '',
                    ];
                })
                ->toArray();
        }

        if (count($staffs) > 0) {
            foreach ($records as $key => $record) {
                if (
                    $record['id'] == ($timeSheet->payroll_supervised_group_id ??
                    $guardScheme->payroll_supervised_group_id)
                ) {
                    foreach ($staffs as $item) {
                        $exist = false;

                        foreach ($record['payroll_staffs'] as $elemento) {
                            if ($elemento['id_number'] === $item['id_number']) {
                                $exist = true;
                                break;
                            }
                        }

                        if (!$exist) {
                            $records[$key]['payroll_staffs'][] = $item;
                        }
                    }
                }
            }
        }

        return response()->json(
            array_merge(
                [
                [
                    'id' => '',
                    'text' => 'Seleccione...',
                ]
                ],
                $records
            ),
            200
        );
    }
}
