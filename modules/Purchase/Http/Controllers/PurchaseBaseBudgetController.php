<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Jobs\PurchaseManageBaseBudget;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;

/**
 * @class PurchaseBaseBudgetController
 * @brief Controlador para la gestión del presupuesto base de compra
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseBaseBudgetController extends Controller
{
    use ValidatesRequests;

    /**
     * Datos de la moneda
     *
     * @var array $currency
     */
    protected $currencies;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // $this->currencies = template_choices('App\Models\Currency', 'name', [], true);
    }

    /**
     * Listado de presupuesto base
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $PurchaseBaseBudget = PurchaseBaseBudget::query()
        ->with([
            'currency',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'pivotRelatable.recordable'
        ])->orderBy('id', 'ASC')
        ->get();

        return response()->json([
            'records' => $PurchaseBaseBudget,
            'message' => 'success',
        ], 200);
    }

    /**
     * Listado de presupuesto base
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $PurchaseBaseBudget = PurchaseBaseBudget::query()
        ->with([
            'currency',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
        ])->orderBy('id', 'ASC')
        ->search($request->query('query'))
        ->paginate($request->limit ?? 10);
        return response()->json([
            'data' => $PurchaseBaseBudget->items(),
            'count' => $PurchaseBaseBudget->total(),
            'message' => 'success',
        ], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de presupuesto base
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $taxes = template_choices('App\Models\Tax', 'name', [], true);

        $requirements = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment',
            'purchaseRequirementItems.measurementUnit',
            'purchaseRequirement.purchaseRequirementItems.historyTax'
        )->where('requirement_status', 'WAIT')->orderBy('code', 'ASC')->get();
        return view('purchase::requirements.base_budget', [
            'requirements' => $requirements,
            'taxes' => json_encode($taxes),
            'currencies' => json_encode($this->currencies),
        ]);
    }

    /**
     * Almacena un nuevo registro de presupuesto base
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'list' => 'required|array',
            'currency_id' => 'required|int',
            'tax_id' => 'required|int',
            'prepared_by_id' => 'required',
        ], [
            'list.required' => 'No es permitido guardar presupuesto base vacios.',
            'list.array' => 'Los registros deben estar en una lista.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.int' => 'El campo moneda debe ser numerico.',
            'tax_id.required' => 'El campo del IVA es obligatorio, verifique que este registrado en la
            configuración base del sistema',
            'tax_id.int' => 'El campo del IVA debe ser numerico.',
            'prepared_by_id.required' => 'El campo de firmar autorizadas, preparado por es obligatorio',
        ]);
        $data = $request->all();
        $data['action'] = 'create';
        PurchaseManageBaseBudget::dispatch($data);
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Muestra información de un presupuesto base
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return response()->json(['records' => PurchaseBaseBudget::with(
            'currency',
            'tax.histories',
            'purchaseRequirement.preparedBy.payrollStaff',
            'purchaseRequirement.reviewedBy.payrollStaff',
            'purchaseRequirement.verifiedBy.payrollStaff',
            'purchaseRequirement.firstSignature.payrollStaff',
            'purchaseRequirement.secondSignature.payrollStaff',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'preparedBy.payrollStaff',
            'reviewedBy.payrollStaff',
            'verifiedBy.payrollStaff',
            'firstSignature.payrollStaff',
            'secondSignature.payrollStaff'
        )->find($id)], 200);
    }

    /**
     * Muestra el formulario para editar un presupuesto base
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $baseBudget = PurchaseBaseBudget::with(
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'purchaseRequirement.purchaseRequirementItems.measurementUnit',
            'purchaseRequirement.purchaseRequirementItems.historyTax',
            'relatable'
        )->find($id);

        /* Se obtienen los datos laborales */
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

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
                    $query->with('user')->where('institution_id', $user_profile->institution_id);
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
        return view('purchase::requirements.base_budget', [
            'requirements' => json_encode([0 => $baseBudget['purchaseRequirement']]),
            'currencies' => json_encode($this->currencies),
            'baseBudget' => $baseBudget,
            'employments' => json_encode($employments),
        ]);
    }

    /**
     * Actualiza la información de un presupuesto base
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'list' => 'required|array',
            'currency_id' => 'required|int',
            'prepared_by_id' => 'required',
        ], [
            'list.required' => 'No es permitido guardar presupuesto base vacios.',
            'list.array' => 'Los registros deben estar en una lista.',
            'currency_id.required' => 'El campo moneda es obligatorio.',
            'currency_id.int' => 'El campo moneda debe ser numerico.',
            'tax_id.int' => 'El campo del IVA debe ser numerico.',
            'prepared_by_id.required' => 'El campo de firmar autorizadas, preparado por es obligatorio',
        ]);

        $data = $request->all();
        $data['id_edit'] = $id;
        $data['action'] = 'update';
        PurchaseManageBaseBudget::dispatch($data);
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Elimina un presupuesto base
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $record = PurchaseBaseBudget::find($id);
        if ($record) {
            foreach (PurchaseRequirement::where('purchase_base_budget_id', $id)->orderBy('id', 'ASC')->get() as $r) {
                $r->delete();
            }
            foreach (
                PurchasePivotModelsToRequirementItem::where('relatable_id', $id)
                ->orderBy('id', 'ASC')->get() as $r
            ) {
                $r->delete();
            }
            $record->delete();
        } else {
            return response()->json(['message' => 'El registro ya fue eliminado.'], 200);
        }
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Envía notificación al usuario seleccionado
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotify(Request $request)
    {
        if ($request->module == 'payroll' && Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $payroll = \Modules\Payroll\Models\Payroll::with('payrollPaymentPeriod')->find($request->id);
            $payroll->payrollPaymentPeriod->availability_status = 'send' ;
            $payroll->payrollPaymentPeriod->save();
        } else {
            $record = PurchaseBaseBudget::find($request->id);
            $record->send_notify = true;
            $record->save();
        }

        $user = User::find($request->user_id);
        $user->notify(new SystemNotification($request->title, $request->details));
        return response()->json([
            'result' => true
        ], 200);
    }
}
