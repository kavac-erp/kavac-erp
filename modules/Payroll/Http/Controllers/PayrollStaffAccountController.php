<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollStaffAccount;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Jobs\PayrollExportNotification;

/**
 * @class PayrollStaffAccountController
 * @brief Clase controlador del expediente de datos contables *
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffAccountController extends Controller
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
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.staffaccount.index', ['only' => 'index']);
        $this->middleware('permission:payroll.staffaccount.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.staffaccount.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.staffaccount.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.staffaccount.import', ['only' => 'import']);
        $this->middleware('permission:payroll.staffaccount.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'accounting_registers.*.accounting_account_id' => ['required'],
            'accounting_registers.*.payroll_staff_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'accounting_registers.*.payroll_staff_id.required'        => 'El campo trabajador es obligatorio.',
            'accounting_registers.*.accounting_account_id.required'        => 'El campo cuenta contable es obligatorio.',
        ];
    }

    /**
     * Muestra todos los registros de expediente de datos contables
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::staff_accounts.index');
    }

    /**
     * Muestra el formulario para crear un nuevo registro de expediente de datos contables
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::staff_accounts.create-edit');
    }

    /**
     * Agrega un nuevo registro de expediente de datos contables
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        foreach ($request->accounting_registers as $account) {
            // Validar cuenta contable con trabajador
            $hasAccountSavedForStaff = PayrollStaffAccount::query()
                ->where('payroll_staff_id', $account['payroll_staff_id'])
                ->where('accounting_account_id', $account['accounting_account_id'])
                ->exists();

            if ($hasAccountSavedForStaff) {
                $errors['error'][0] = $account['payroll_staff'] . ' ya existe con esta cuenta contable: ' . $account['accounting_account'];
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $errors], 422);
            }
        }
        DB::transaction(
            function () use ($request) {
                foreach ($request->accounting_registers as $account) {
                    $payrollStaff = PayrollStaffAccount::create(
                        [
                            'payroll_staff_id' => $account['payroll_staff_id'],
                            'accounting_account_id' => $account['accounting_account_id'],
                        ]
                    );
                }
            }
        );
        return response()->json(['message' => 'Success', 'redirect' => route('payroll.staff-accounts.index')], 200);
    }

    /**
     * Muestra información de un expediente de datos contables
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $payrollStaffAccount = PayrollStaffAccount::find($id);
        return response()->json(['record' => $payrollStaffAccount], 200);
    }

    /**
     * Muestra el formulario de actualización de expediente de datos contables
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::staff_accounts.create-edit', ['id' => $id]);
    }

    /**
     * Actualiza el expediente de datos contables
     *
     * @param object    Request $request Datos de la petición
     * @param integer           $id      Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollStaffAccount = PayrollStaffAccount::find($id);
        $first = true;

        $this->validate($request, $this->validateRules, $this->messages);

        foreach ($request->accounting_registers as $account) {
            // Validar cuenta contable con trabajador
            $hasAccountSavedForStaff = PayrollStaffAccount::query()
                ->where('payroll_staff_id', $account['payroll_staff_id'])
                ->where('accounting_account_id', $account['accounting_account_id'])
                ->exists();

            if ($hasAccountSavedForStaff) {
                $errors['error'][0] = $account['payroll_staff'] . ' ya existe con esta cuenta contable: ' . $account['accounting_account'];
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $errors], 422);
            }
        }

        foreach ($request->accounting_registers as $account) {
            if ($first) {
                $payrollStaffAccount->payroll_staff_id = $account['payroll_staff_id'];
                $payrollStaffAccount->accounting_account_id = $account['accounting_account_id'];
                $payrollStaffAccount->save();
            } else {
                $payrollStaff = PayrollStaffAccount::create(
                    [
                        'payroll_staff_id' => $account['payroll_staff_id'],
                        'accounting_account_id' => $account['accounting_account_id'],
                    ]
                );
            }
            $first = false;
        }

        return response()->json(['message' => 'Success', 'redirect' => route('payroll.staff-accounts.index')], 200);
    }

    /**
     * Elimina el expediente de datos contables
     *
     * @param integer $id Identificador del registro
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $payrollStaffAccount = PayrollStaffAccount::find($id);
        $payrollStaffAccount->delete();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Muestra los datos contables registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $records = PayrollStaffAccount::query()
            ->with('payrollStaff', 'accountingAccount')
            ->search($request->get('query'))
            ->paginate($request->get('limit'));

        return response()->json([
            'data' => $records->items(),
            'count' => $records->total(),
        ]);
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
            'Datos Contables',
        );

        request()->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
                'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.staff-accounts.index');
    }

    /**
     * Realiza la acción necesaria para importar los datos contables
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
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
}
