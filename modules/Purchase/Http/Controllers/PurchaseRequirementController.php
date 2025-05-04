<?php

namespace Modules\Purchase\Http\Controllers;

use App\Exceptions\ClosedFiscalYearException;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Institution;
use App\Models\Profile;
use App\Rules\DateBeforeFiscalYear;
use DateTime;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Purchase\Jobs\PurchaseManageRequirements;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Models\PurchaseRequirementItem;
use Modules\Purchase\Models\PurchaseSupplierObject;
use Modules\Warehouse\Models\WarehouseProduct;
use Nwidart\Modules\Facades\Module;

class PurchaseRequirementController extends Controller
{
    use ValidatesRequests;

    protected $supplier_objects;
    // protected $currencies;

    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:purchase.requirements.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:purchase.requirements.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase.requirements.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase.requirements.delete', ['only' => 'destroy']);

        $supplier_objects = [
            ['id' => '', 'text' => 'Seleccione...'],
            ['id' => 'Bienes', 'text' => []],
            ['id' => 'Obras', 'text' => []],
            ['id' => 'Servicios', 'text' => []],
        ];

        foreach (PurchaseSupplierObject::all() as $so) {
            $type = ($so->type === 'B') ? 'Bienes' : (($so->type === 'O') ? 'Obras' : 'Servicios');

            for ($i = 1; $i < count($supplier_objects); $i++) {
                if ($type == $supplier_objects[$i]['id'] && is_array($supplier_objects[$i]['text'])) {
                    $supplier_objects[$i]['text'][$so->id] = $so->name;
                }
            }
        }

        $this->supplier_objects = $supplier_objects;
        // $this->currencies = template_choices('App\Models\Currency', 'name', [], true);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];
        //datos de los empleados que tiene asociado un perfilñ de usuario
        $employments_users = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        $requirements = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment'
        )->orderBy('code', 'ASC')->get();
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $institution = Institution::where([
            'active'    => true,
            'default'   => true,
        ])->first();

        $institution_id = $user_profile
        ? $user_profile->institution_id
        ?? ($institution ? $institution->id : 1)
        : 1;

        $employment_with_users = PayrollEmployment::whereHas(
            'profile',
            function ($query) use ($institution_id) {
                $query->whereHas('user')
                ->where('institution_id', $institution_id);
            }
        )->with([
            'payrollStaff', 'profile' => function ($query) use ($institution_id) {
                $query->with('user')
                ->where('institution_id', $institution_id);
            }
        ])->get();

        if ($employment_with_users) {
            foreach ($employment_with_users as $emp_user) {
                if ($emp_user->profile && $emp_user->profile->user_id) {
                    $text = '';
                    if ($emp_user->payrollStaff->id_number) {
                        $text = $emp_user->payrollStaff->id_number . ' - ' .
                        $emp_user->payrollStaff->first_name . ' ' . $emp_user->payrollStaff->last_name;
                    } else {
                        $text = $emp_user->payrollStaff->passport . ' - ' .
                        $emp_user->payrollStaff->first_name . ' ' . $emp_user->payrollStaff->last_name;
                    }
                    array_push($employments_users, [
                        'id' => $emp_user->profile->user_id,
                        'text' => $text,
                    ]);
                }
            }
        }

        if ($user_profile && $user_profile->institution !== null) {
            foreach (
                PayrollEmployment::with('payrollStaff', 'profile')
                ->whereHas('profile', function ($query) use ($user_profile) {
                    $query->where('institution_id', $user_profile->institution_id);
                })->get() as $key => $employment
            ) {
                $text = '';
                if ($employment->payrollStaff !== null) {
                    if ($employment->payrollStaff->id_number) {
                        $text = $employment->payrollStaff->id_number . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    } else {
                        $text = $employment->payrollStaff->passport . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    }
                    array_push($employments, [
                        'id' => $employment->id,
                        'text' => $text,
                    ]);
                }
            }
        } else {
            foreach (PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
                $text = '';
                if ($employment->payrollStaff !== null) {
                    if ($employment->payrollStaff->id_number) {
                        $text = $employment->payrollStaff->id_number . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    } else {
                        $text = $employment->payrollStaff->passport . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    }
                    array_push($employments, [
                        'id' => $employment->id,
                        'text' => $text,
                    ]);
                }
            }
        }

        $codeAvailable = (CodeSetting::where('table', 'purchase_requirements')->first()) ? true : false;
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

        return view(
            'purchase::requirements.index',
            [
                'requirements' => $requirements,
                'has_budget' => $has_budget,
                'employments' => json_encode($employments),
                'codeAvailable' => $codeAvailable,
                'employments_users' => json_encode($employments_users),
                'has_availability_request_permission' => json_encode(auth()->user()->hasPermission('purchase.availability.request')),
            ]
        );
    }

    /**
     * create the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $institutions = template_choices('App\Models\Institution', 'name', [], true);
        $measurement_units = template_choices('App\Models\MeasurementUnit', 'name', [], true);

        $supplier_objects = $this->supplier_objects;

        $purchase_supplier_objects = [];

        array_push(
            $purchase_supplier_objects,
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        );

        foreach (PurchaseSupplierObject::all() as $record) {
            $type = $record->type;
            if ($type == 'B') {
                $type = 'Bienes';
            } elseif ($type == 'O') {
                $type = 'Obras';
            } elseif ($type == 'S') {
                $type = 'Servivios';
            }
            array_push(
                $purchase_supplier_objects,
                [
                    'id' => $record->id,
                    'text' => $type . ' - ' . $record->name,
                ],
            );
        }

        $date = date('Y-m-d');

        /**
         * Se obtienen los datos laborales
         */
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        if ($user_profile && $user_profile->institution !== null) {
            foreach (
                PayrollEmployment::with('payrollStaff', 'profile')
                ->whereHas('profile', function ($query) use ($user_profile) {
                    $query->where('institution_id', $user_profile->institution_id);
                })->get() as $key => $employment
            ) {
                if (isset($employment->payrollStaff)) {
                    $text = '';
                    if (isset($employment->payrollStaff->id_number) && $employment->payrollStaff->id_number) {
                        $text = $employment->payrollStaff->id_number . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    } else {
                        $text = $employment->payrollStaff->passport . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    }
                    array_push($employments, [
                        'id' => $employment->id,
                        'text' => $text,
                    ]);
                }
            }
        } else {
            foreach (PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
                if (isset($employment->payrollStaff)) {
                    $text = '';

                    if (isset($employment->payrollStaff->id_number) && $employment->payrollStaff->id_number) {
                        $text = $employment->payrollStaff->id_number . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    } else {
                        $text = $employment->payrollStaff->passport . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    }
                    array_push($employments, [
                        'id' => $employment->id,
                        'text' => $text,
                    ]);
                }
            }
        }
        return view(
            'purchase::requirements.form',
            [
                'supplier_objects' => json_encode($supplier_objects),
                'date' => json_encode($date),
                'institutions' => json_encode($institutions),
                'purchase_supplier_objects' => json_encode($purchase_supplier_objects),
                'measurement_units' => json_encode($measurement_units),
                'employments' => json_encode($employments),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $date = new DateTime($request->date);
        $formatedDate = $date->format('Y');

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::query()
                ->where(['id' => auth()->user()->profile->institution_id])
                ->first();
        } else {
            $institution = Institution::query()
                ->where(['active' => true, 'default' => true])
                ->first();
        }

        $currentFiscalYear = FiscalYear::query()
            ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($currentFiscalYear->entries)) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar ' .
                   'registros debido a que se está realizando el cierre de año fiscal')
            );
        }

        $closedFiscalYear = FiscalYear::query()
            ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($closedFiscalYear) && $formatedDate == $closedFiscalYear->year) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar registros de un año fiscal cerrado')
            );
        }

        $this->validate($request, [
            'date' => ['required', new DateBeforeFiscalYear('fecha de generación')],
            'description' => 'required|string',
            'institution_id' => 'required|integer',
            'prepared_by_id' => 'required',
            'contracting_department_id' => 'required|integer',
            'user_department_id' => 'required|integer',
            'purchase_supplier_object_id' => 'required|integer',
            'products' => 'required',
            'products.*.quantity' => 'required|gt:0',
            'products.*.technical_specifications' => 'required',
            'requirement_type' => 'required',
            'products.*.history_tax_id' => 'required',
            'products.*.measurement_unit_id' => 'required',
        ], [
            'date.required' => 'El campo fecha es obligatorio.',
            'description.required' => 'El campo descripción es obligatorio.',
            'institution_id.required' => 'El campo institución es obligatorio.',
            'institution_id.integer' => 'El campo institución no esta en el formato de entero.',
            'prepared_by_id.required' => 'El campo preparado por es obligatorio.',
            'contracting_department_id.required' => 'El campo unidad contratante es obligatorio.',
            'contracting_department_id.integer' => 'El campo unidad contratante no esta en el formato de entero.',
            'user_department_id.required' => 'El campo unidad usuaria es obligatorio.',
            'user_department_id.integer' => 'El campo unidad usuaria no esta en el formato de entero.',
            'purchase_supplier_object_id.required' => 'El campo tipo es obligatorio.',
            'purchase_supplier_object_id.integer' => 'El campo tipo no esta en el formato de entero.',
            'products.required' => 'El campo producto es obligatorio',
            'products.*.quantity.required' => 'El campo cantidad de producto es obligatorio',
            'products.*.quantity.gt' => 'El campo cantidad de producto debe ser mayor que cero.',
            'products.*.technical_specifications.required' => '
                El campo especificaciones técnicas del producto es obligatorio.
            ',
            'requirement_type.required' => 'El campo tipo de requerimiento es obligatorio.',
            'products.*.history_tax_id.required' => 'El campo impuesto del producto es obligatorio.',
            'products.*.measurement_unit_id.required' => 'El campo unidad de medida del producto es obligatorio.',
        ]);

        $data = $request->all();
        $data['action'] = 'create';

        PurchaseManageRequirements::dispatch($data);
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * [generateReferenceCodeAvailable genera el código disponible]
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @return string código que se asignara
     */
    public function generateReferenceCodeAvailable()
    {
        $institution = $this->getInstitution();
        $codeSetting = CodeSetting::where('table', $institution->id . '_'
            . $institution->acronym . '_accounting_entries')->first();

        if (!$codeSetting) {
            $codeSetting = CodeSetting::where('table', 'accounting_entries')
                ->first();
        }

        if ($codeSetting) {
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

            $code = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                AccountingEntry::class,
                $codeSetting->field
            );
        } else {
            $code = 'error al generar código de referencia';
        }

        return $code;
    }

    /**
     * Show the specified resource.
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json(['records' => PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment',
            'purchaseSupplierObject',
            'purchaseRequirementItems.measurementUnit',
            'preparedBy.payrollStaff',
            'reviewedBy.payrollStaff',
            'verifiedBy.payrollStaff',
            'firstSignature.payrollStaff',
            'secondSignature.payrollStaff'
        )->find($id)], 200);
    }

    /**
     * edit the form for editing the specified resource.
     * @return Renderable
     */
    public function edit($id)
    {
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $requirement_edit = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment',
            'purchaseRequirementItems.measurementUnit'
        )->find($id);
        $institutions = template_choices('App\Models\Institution', 'name', [], true);
        $department_list = template_choices('App\Models\Department', 'name', [], true);
        $measurement_units = template_choices('App\Models\MeasurementUnit', 'name', [], true);

        $supplier_objects = $this->supplier_objects;

        $purchase_supplier_objects = [];

        array_push(
            $purchase_supplier_objects,
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        );

        foreach (PurchaseSupplierObject::all() as $record) {
            $type = $record->type;
            if ($type == 'B') {
                $type = 'Bienes';
            } elseif ($type == 'O') {
                $type = 'Obras';
            } elseif ($type == 'S') {
                $type = 'Servivios';
            }
            array_push(
                $purchase_supplier_objects,
                [
                    'id' => $record->id,
                    'text' => $type . ' - ' . $record->name,
                ],
            );
        }

        $warehouses = template_choices(
            'Modules\Warehouse\Models\Warehouse',
            ['name', 'measurement_unit_id'],
            [],
            true
        );

        //$date = date('Y-m-d');
        /* fecha ingresada por el usuario */
        $date = $requirement_edit['date'];

        /**
         * Se obtienen los datos laborales
         */
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        if ($user_profile && $user_profile->institution !== null) {
            foreach (
                PayrollEmployment::with('payrollStaff', 'profile')
                ->whereHas('profile', function ($query) use ($user_profile) {
                    $query->where('institution_id', $user_profile->institution_id);
                })->get() as $key => $employment
            ) {
                $text = '';
                if ($employment->payrollStaff !== null) {
                    if ($employment->payrollStaff->id_number) {
                        $text = $employment->payrollStaff->id_number . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    } else {
                        $text = $employment->payrollStaff->passport . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    }
                    array_push($employments, [
                        'id' => $employment->id,
                        'text' => $text,
                    ]);
                }
            }
        } else {
            foreach (PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
                $text = '';
                if ($employment->payrollStaff !== null) {
                    if ($employment->payrollStaff->id_number) {
                        $text = $employment->payrollStaff->id_number . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    } else {
                        $text = $employment->payrollStaff->passport . ' - ' .
                        $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                    }
                    array_push($employments, [
                        'id' => $employment->id,
                        'text' => $text,
                    ]);
                }
            }
        }

        return view(
            'purchase::requirements.form',
            [
                'supplier_objects' => json_encode($supplier_objects),
                'date' => json_encode($date),
                'institutions' => json_encode($institutions),
                'department_list' => json_encode($department_list),
                'purchase_supplier_objects' => json_encode($purchase_supplier_objects),
                'measurement_units' => json_encode($measurement_units),
                'requirement_edit' => $requirement_edit,
                'employments' => json_encode($employments),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $date = new DateTime($request->date);
        $formatedDate = $date->format('Y');

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::query()
                ->where(['id' => auth()->user()->profile->institution_id])
                ->first();
        } else {
            $institution = Institution::query()
                ->where(['active' => true, 'default' => true])
                ->first();
        }

        $currentFiscalYear = FiscalYear::query()
            ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($currentFiscalYear->entries)) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar ' .
                   'registros debido a que se está realizando el cierre de año fiscal')
            );
        }

        $closedFiscalYear = FiscalYear::query()
            ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
            ->orderBy('year', 'desc')
            ->first();

        if (isset($closedFiscalYear) && $formatedDate == $closedFiscalYear->year) {
            return throw new ClosedFiscalYearException(
                __('No puede registrar, actualizar o eliminar registros de un año fiscal cerrado')
            );
        }

        $this->validate($request, [
            'date' => ['required', new DateBeforeFiscalYear('fecha de generación')],
            'description' => 'required|string',
            'institution_id' => 'required|integer',
            'prepared_by_id' => 'required',
            'contracting_department_id' => 'required|integer',
            'user_department_id' => 'required|integer',
            'purchase_supplier_object_id' => 'required|integer',
            'products' => 'required',
            'products.*.quantity' => 'required|gt:0',
            'products.*.technical_specifications' => 'required',
            'products.*.history_tax_id' => 'required',
            'products.*.measurement_unit_id' => 'required',
        ], [
            'date.required' => 'El campo fecha es obligatorio.',
            'description.required' => 'El campo descripción es obligatorio.',
            'institution_id.required' => 'El campo institución es obligatorio.',
            'institution_id.integer' => 'El campo institución no esta en el formato de entero.',
            'prepared_by_id.required' => 'El campo preparado por es obligatorio.',
            'contracting_department_id.required' => 'El campo unidad contratante es obligatorio.',
            'contracting_department_id.integer' => 'El campo unidad contratante no esta en el formato de entero.',
            'user_department_id.required' => 'El campo unidad usuaria es obligatorio.',
            'user_department_id.integer' => 'El campo unidad usuaria no esta en el formato de entero.',
            'purchase_supplier_object_id.required' => 'El campo tipo es obligatorio.',
            'purchase_supplier_object_id.integer' => 'El campo tipo no esta en el formato de entero.',
            'products.required' => 'El campo producto es obligatorio.',
            'products.*.quantity.required' => 'El campo cantidad de producto es obligatorio.',
            'products.*.quantity.gt' => 'El campo cantidad de producto debe ser mayor que cero',
            'products.*.technical_specifications.required' => '
                El campo especificaciones técnicas del producto es obligatorio.
            ',
            'products.*.history_tax_id.required' => 'El campo impuesto del producto es obligatorio.',
            'products.*.measurement_unit_id.required' => 'El campo unidad de medida del producto es obligatorio.',
        ]);

        $data = $request->all();
        $data['id_edit'] = $id;
        $data['action'] = 'update';
        PurchaseManageRequirements::dispatch($data);
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @return JsonResponse
     */
    public function destroy($id)
    {
        foreach (PurchaseRequirementItem::where('purchase_requirement_id', $id)->get() as $record) {
            $record->delete();
        }
        $record = PurchaseRequirement::find($id);
        if ($record) {
            $record->delete();
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function getRequirementItems($id)
    {
        $items = PurchaseRequirementItem::where('purchase_requirement_id', $id)->get();
        return response()->json(['items' => $items], 200);
    }

    public function getWarehouseProducts()
    {
        $records = [];
        foreach (WarehouseProduct::with('measurementUnit')->orderBy('id', 'ASC')->get() as $record) {
            array_push($records, [
                'id' => $record->id,
                'text' => $record->name,
                'measurement_unit' => $record->measurement_unit['name'],
            ]);
        }
        return $records;
    }

    /**
     * Devuelve un listado con los registros de requerimientos
     * @return JsonResponse
     */
    public function getRequirements()
    {
        return response()->json([
            'records' => template_choices('App\Models\Currency', 'code', [], true)
        ], 200);
    }
}
