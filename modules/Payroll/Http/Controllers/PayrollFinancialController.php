<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollFinancial;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\Imports\Staff\RegisterStaffImport;
use Modules\Payroll\Jobs\PayrollExportNotification;

/**
 * @class PayrollFinancialController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFinancialController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.financials.create', ['only' => ['store', 'create']]);
        $this->middleware('permission:payroll.financials.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.financials.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.financials.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.financials.import', ['only' => 'import']);
        $this->middleware('permission:payroll.financials.export', ['only' => 'export']);
    }

    /**
     * Muestra el listados de datos financieros
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::financials.index');
    }

    /**
     * Muestra el formulario de registro de datos financieros
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::financials.create-edit');
    }

    /**
     * Metodo que almacena un nuevo registro de datos financieros
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'payroll_staff_id'        => ['required','unique:payroll_financials,payroll_staff_id'],
            'finance_bank_id'         => ['required'],
            'finance_account_type_id' => ['required'],
            'payroll_account_number'  => ['required', 'numeric', 'digits_between:20,20', 'unique:payroll_financials']
        ], [
            'payroll_staff_id.required' => 'El campo trabajador es obligatorio.',
            'payroll_staff_id.unique' => 'El trabajador ya tiene registrado datos financieros.',
            'finance_bank_id.required' => 'El campo banco es obligatorio.',
            'finance_account_type_id.required' => 'El campo tipo de cuenta es obligatorio.',
            'payroll_account_number.required'  => 'El campo número de cuenta es obligatorio.',
            'payroll_account_number.numeric'   => 'El campo número de cuenta debe se númerico',
            'payroll_account_number.digits_between'       => 'El campo número de cuenta debe tener 20 dígitos',
            'payroll_account_number.unique' => 'El campo  número de cuenta ya ha sido registrado.',
        ]);

        $payrollFinancial = PayrollFinancial::create([
            'payroll_staff_id'        => $request->payroll_staff_id,
            'finance_bank_id'         => $request->finance_bank_id,
            'finance_account_type_id' => $request->finance_account_type_id,
            'payroll_account_number'  => $request->payroll_account_number,
        ]);

        $request->session()->flash('message', ['type' => 'store']);

        return response()->json(['result' => true, 'redirect' => route('payroll.financials.index')], 200);
    }

    /**
     * Muestra información de datos financieros
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
     * Muestra el formulario de edición de datos financieros
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        /* Objeto asociado al modelo PayrollFinancial */
        $payrollfinancial_edit = PayrollFinancial::find($id);
        return view('payroll::financials.create-edit', ['payrollfinancial_edit' => $payrollfinancial_edit ]);
    }

    /**
     * Realiza la acción necesaria para importar los datos Financieros
     *
     * @author    Francisco Escala
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    object    Objeto que permite descargar el archivo con la información a ser exportada
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
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        $userId = auth()->user()->id;
        PayrollExportNotification::dispatch(
            $userId,
            'Datos Financieros',
        );

        request()->session()->flash('message', ['type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
            'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return redirect()->route('payroll.financials.index');
    }

    /**
     * Actualiza los datos financieros
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //$this->validate($request, $this->validateRules, $this->messages);
        $this->validate($request, [
            'payroll_staff_id'        => ['required'],
            'finance_bank_id'         => ['required'],
            'finance_account_type_id' => ['required'],
            'payroll_account_number'  => ['required', 'numeric', 'digits_between:20,20', 'unique:payroll_financials,payroll_account_number, ' . $id],
            'payroll_account_number.unique' => 'El campo  número de cuenta ya ha sido registrado.',
        ], [
            'payroll_staff_id.required' => 'El campo trabajador es obligatorio.',
            'payroll_staff_id.unique' => 'El trabajador ya tiene registrado datos financieros.',
            'finance_bank_id.required'         => 'El campo banco es obligatorio.',
            'finance_account_type_id.required' => 'El campo tipo de cuenta es obligatorio.',
            'payroll_account_number.required'  => 'El campo número de cuenta es obligatorio.',
            'payroll_account_number.numeric'   => 'El campo número de cuenta debe se númerico',
            'payroll_account_number.digits_between'       => 'El campo número de cuenta debe tener 20 dígitos',
            'payroll_account_number.unique' => 'El campo  número de cuenta ya ha sido registrado.',
        ]);
        $payrollFinancial = PayrollFinancial::find($id);
        $payrollFinancial->payroll_staff_id = $request->payroll_staff_id;
        $payrollFinancial->finance_bank_id = $request->finance_bank_id;
        $payrollFinancial->finance_account_type_id = $request->finance_account_type_id;
        $payrollFinancial->payroll_account_number = $request->payroll_account_number;
        $payrollFinancial->save();

        $request->session()->flash('message', ['type' => 'update']);

        return response()->json(['result' => true, 'redirect' => route('payroll.financials.index')], 200);
    }

    /**
     * Elimina los datos financieros
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollFinancial = PayrollFinancial::find($id);
        $payrollFinancial->delete();

        session()->flash('message', ['type' => 'destroy']);

        return response()->json(['record' => $payrollFinancial, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los datos laborales registrados
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Json con los datos financieros del trabajador
     */
    public function vueList(Request $request)
    {
        $records = PayrollFinancial::with([
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
            'financeBank',
            'financeAccountType'
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
