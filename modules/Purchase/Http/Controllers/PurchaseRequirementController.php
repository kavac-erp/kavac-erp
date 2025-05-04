<?php

namespace Modules\Purchase\Http\Controllers;

use DateTime;
use App\Models\Profile;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Rules\DateBeforeFiscalYear;
use Nwidart\Modules\Facades\Module;
use App\Exceptions\ClosedFiscalYearException;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Models\PurchaseSupplierObject;
use Modules\Purchase\Models\PurchaseRequirementItem;
use Modules\Purchase\Jobs\PurchaseManageRequirements;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class PurchaseRequirementController
 * @brief Gestiona los procesos de los requerimientos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseRequirementController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de objetos de proveedores
     *
     * @var array $supplier_objects
     */
    protected $supplier_objects;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
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
    }

    /**
     * Muestra el listado de requerimientos de compra
     *
     * @return \Illuminate\View\View
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

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $institution = Institution::where([
            'active'    => true,
            'default'   => true,
        ])->first();

        $institution_id = $user_profile
        ? $user_profile->institution_id
        ?? ($institution ? $institution->id : 1)
        : 1;

        $employment_with_users = (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? \Modules\Payroll\Models\PayrollEmployment::whereHas(
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
        ])->get() : [];

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

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            if ($user_profile && $user_profile->institution !== null) {
                foreach (
                    \Modules\Payroll\Models\PayrollEmployment::with('payrollStaff', 'profile')
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
                foreach (\Modules\Payroll\Models\PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
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
        }

        $codeAvailable = (CodeSetting::where('table', 'purchase_requirements')->first()) ? true : false;
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

        return view(
            'purchase::requirements.index',
            [
                'has_budget' => $has_budget,
                'employments' => json_encode($employments),
                'codeAvailable' => $codeAvailable,
                'employments_users' => json_encode($employments_users),
                'has_availability_request_permission' => json_encode(auth()->user()->hasPermission('purchase.availability.request')),
            ]
        );
    }

    /**
     * Listado de requerimientos de compra.
     *
     * @param \Illuminate\Http\Request $request Datos de la petición.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $requirements = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment'
        )->orderBy('id', 'ASC')
        ->orderBy('date', 'ASC')
        ->search($request->query('query'))
        ->paginate($request->limit ?? 10);

        return response()->json([
            'data' => $requirements->items(),
            'count' => $requirements->total(),
            'message' => 'success',
        ], 200);
    }

    /**
     * Muestra el formulario de creación de requerimientos de compra.
     *
     * @return \Illuminate\View\View
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

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            if ($user_profile && $user_profile->institution !== null) {
                foreach (
                    \Modules\Payroll\Models\PayrollEmployment::with('payrollStaff', 'profile')
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
                foreach (\Modules\Payroll\Models\PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
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
     * Almacena un nuevo requerimiento de compra.
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Genera el código disponible para el requerimiento de compra
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string código que se asignara
     */
    public function generateReferenceCodeAvailable()
    {
        if (!Module::has('Accounting') || !Module::isEnabled('Accounting')) {
            return '';
        }
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
                \Modules\Accounting\Models\AccountingEntry::class,
                $codeSetting->field
            );
        } else {
            $code = 'error al generar código de referencia';
        }

        return $code;
    }

    /**
     * Muestra información de un requerimiento de compra
     *
     * @return \Illuminate\Http\Response
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
     * Muestra el formulario de edición de un requerimiento de compra
     *
     * @return \Illuminate\View\View
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

        /* fecha ingresada por el usuario */
        $date = $requirement_edit['date'];

        /* Se obtienen los datos laborales */
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            if ($user_profile && $user_profile->institution !== null) {
                foreach (
                    \Modules\Payroll\Models\PayrollEmployment::with('payrollStaff', 'profile')
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
                foreach (\Modules\Payroll\Models\PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
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
     * Actualiza un requerimiento de compra.
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
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
     * Elimina un requerimiento de compra.
     *
     * @return \Illuminate\Http\JsonResponse
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

    /**
     * Obtiene los items de un requerimiento de compra
     *
     * @param integer $id ID del requerimiento de compra
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequirementItems($id)
    {
        $items = PurchaseRequirementItem::where('purchase_requirement_id', $id)->get();
        return response()->json(['items' => $items], 200);
    }

    /**
     * Obtiene los productos de almacén
     *
     * @return array
     */
    public function getWarehouseProducts()
    {
        $records = [];
        if (Module::has('Warehouse') && Module::isEnabled('Warehouse')) {
            foreach (\Modules\Warehouse\Models\WarehouseProduct::with('measurementUnit')->orderBy('id', 'ASC')->get() as $record) {
                array_push($records, [
                    'id' => $record->id,
                    'text' => $record->name,
                    'measurement_unit' => $record->measurement_unit['name'],
                ]);
            }
        }
        return $records;
    }

    /**
     * Devuelve un listado con los registros de requerimientos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequirements()
    {
        return response()->json([
            'records' => template_choices('App\Models\Currency', 'code', [], true)
        ], 200);
    }
}
