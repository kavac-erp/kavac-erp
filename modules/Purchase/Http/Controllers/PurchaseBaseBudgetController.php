<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\Profile;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Purchase\Jobs\PurchaseManageBaseBudget;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;
use Modules\Purchase\Models\PurchaseRequirement;
use App\Models\User;
use App\Notifications\SystemNotification;
use Nwidart\Modules\Facades\Module;

class PurchaseBaseBudgetController extends Controller
{
    use ValidatesRequests;

    protected $currencies;

    public function __construct()
    {
        // $this->currencies = template_choices('App\Models\Currency', 'name', [], true);
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json([
            'records' => PurchaseBaseBudget::with([
                'currency',
                'purchaseRequirement.contratingDepartment',
                'purchaseRequirement.userDepartment',
                'relatable.purchaseRequirementItem.purchaseRequirement',
                'pivotRelatable' => function ($q) {
                    $q->with(['recordable' => function ($query) {
                    }])->get();
                },
            ])->orderBy('id', 'ASC')->get(),
            'message' => 'success',
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
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
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return JsonResponse
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
     * Show the specified resource.
     * @return JsonResponse
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
     * Show the form for editing the specified resource.
     * @return Renderable
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

        /**
         * Se obtienen los datos laborales
         */
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
        // $requirements = PurchaseRequirement::with(
        //     'contratingDepartment',
        //     'userDepartment',
        //     'purchaseRequirementItems'
        // )->where('requirement_status', 'WAIT')->orderBy('code', 'ASC')->get();
        return view('purchase::requirements.base_budget', [
            'requirements' => json_encode([0 => $baseBudget['purchaseRequirement']]),
            'currencies' => json_encode($this->currencies),
            'baseBudget' => $baseBudget,
            'employments' => json_encode($employments),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return JsonResponse
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
     * Remove the specified resource from storage.
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $record = PurchaseBaseBudget::find($id);
        if ($record) {
            foreach (PurchaseRequirement::where('purchase_base_budget_id', $id)->orderBy('id', 'ASC')->get() as $r) {
                // $r->purchase_base_budget_id = null;
                // $r->requirement_status = 'WAIT';
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
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
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
