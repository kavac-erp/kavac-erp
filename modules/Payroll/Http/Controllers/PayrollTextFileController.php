<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Profile;
use App\Models\Currency;
use App\Models\Parameter;
use App\Models\FiscalYear;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\Payroll;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Validator;
use Modules\Payroll\Models\PayrollTextFile;
use Modules\Payroll\Exports\PayrollPdfExport;
use Modules\Payroll\Models\PayrollPaymentType;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Exports\PayrollTextFileExport;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class PayrollTextFileController
 * @brief Controlador para los archivos de texto de nomina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTextFileController extends Controller
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
     * Números decimales a mostrar
     *
     * @var Parameter $number_decimals
     */
    protected $number_decimals;

    /**
     * Función de redondeo a utilizar
     *
     * @var Parameter $round
     */
    protected $round;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:payroll.txt.file.create', ['only' => ['index', 'create']]);
        $this->middleware('permission:payroll.budget.report.getbudgetaccountingreport', ['only' => 'getBudgetAccountingReport']);


        $this->rules = [
            'fileName'          => ['required'],
            'fileNumber'        => ['required', 'numeric'],
            'payrollId'         => ['required', 'array', 'min:1'],
            'payrollId.*'       => ['required'],
            'date'              => ['required', 'date'],
        ];

        $this->messages = [
            'fileName.required'             => 'El nombre del archivo es obligatorio',
            'fileNumber.required'           => 'El número de archivo es obligatorio',
            'fileNumber.numeric'            => 'El número de archivo debe ser numérico',
            'payrollId.required'            => '
                El campo nómina es obligatorio y debe contener al menos un elemento seleccionado
            ',
            'date.required'                 => 'La fecha de pago es obligatoria',
            'date.date'                     => 'La fecha de pago tiene un formato inválido',
        ];
    }

    /**
     * Muestra el listado de archivos de texto de nómina
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('payroll::payroll_text_file.index');
    }

    /**
     * Muestra el formulario de creación de archivos de texto de nómina
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::payroll_text_file.create-edit');
    }

    /**
     * Muestra el formulario de edición de archivos de texto de nómina
     *
     * @param integer $id id del archivo de texto de nómina
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $payrollTextFile = PayrollTextFile::find($id);

        return view('payroll::payroll_text_file.create-edit', compact('payrollTextFile'));
    }

    /**
     * Valida los datos del archivo de texto de nómina
     *
     * @param \Illuminate\Http\JsonResponse
     */
    public function validateTxtData(Request $request)
    {
        $validator = Validator::make($request->toArray(), $this->rules, $this->messages);

        if (!$validator->fails()) {
            return response()->json(['message' => 'Success'], 200);
        }

        return response()->json(['errors' => $validator->errors()], 422);
    }

    /**
     * Elimina un archivo de texto de nómina
     *
     * @param integer $id id del archivo de texto de nómina
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTextFileRecord($id)
    {
        $payrollTextFile = PayrollTextFile::find($id);
        $payrollTextFile->delete();
        return response()->json(['record' => $payrollTextFile, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un archivo de texto de nómina
     *
     * @param integer $id id del archivo de texto de nómina
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editTextFileRecord($id)
    {
        return response()->json(['record' => PayrollTextFile::find($id), 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los archivos de texto de nómina
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTextFileRecords()
    {
        return response()->json(['records' => PayrollTextFile::all(), 'message' => 'Success'], 200);
    }

    /**
     * Descarga un archivo de texto de nómina
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return BinaryFileResponse
     */
    public function downloadFile(Request $request)
    {
        $this->validate($request, $this->rules, $this->messages);

        $request['payrollId'] = array_map(function ($payrollId) {
            if (is_string($payrollId)) {
                $payroll = json_decode($payrollId, true);
                return $payroll;
            }
            return $payrollId;
        }, $request['payrollId']);

        $payroll_ids = array_column($request['payrollId'], 'id');

        $request['bankAccount'] = $request["payrollId"][0]['bank_account'];

        $export = new PayrollTextFileExport();

        $export->setPayrollId($payroll_ids, $request['bankAccount'], $request['fileNumber'], $request['date']);

        return Excel::download($export, $request['fileNumber'] . $request["fileName"] . now('utc') . '.txt', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Obtiene la lista de archivos de texto de nómina
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayrollList()
    {
        $records = [];
        $a = [];
        $bank_accounts = [];

        $payrolls = Payroll::whereHas('payrollPaymentPeriod', function ($query) {
            $query->where('payment_status', 'generated');
        })->get()->toArray();

        foreach ($payrolls as $payroll) {
            if (
                !in_array(
                    $payroll['payroll_payment_period']['payroll_payment_type']['finance_bank_account']['formated_ccc_number'],
                    $bank_accounts
                )
            ) {
                $bank_accounts[] = $payroll['payroll_payment_period']['payroll_payment_type']['finance_bank_account']['formated_ccc_number'];
            }
        }

        foreach ($payrolls as $payroll) {
            if (!array_key_exists('account', $records)) {
                $records['account'] =
                    [
                        'label' => $payroll['payroll_payment_period']['payroll_payment_type']['finance_bank_account']['formated_ccc_number'],

                        'group' => [
                            0 => [
                                'id' => $payroll["id"],
                                'text' => $payroll["name"],
                                'bank_account' => $payroll["payroll_payment_period"]["payroll_payment_type"]["finance_bank_account"]['ccc_number']
                            ]
                        ]
                    ];
            } else {
                $records['account']['group'][] =
                    [
                        'id' => $payroll["id"],
                        'text' => $payroll["name"],
                        'bank_account' => $payroll["payroll_payment_period"]["payroll_payment_type"]["finance_bank_account"]['ccc_number']
                    ];
            }
        }

        foreach ($records as $record) {
            array_push($a, $record);
        }

        return response()->json(['payroll_list' => $a], 200);
    }

    /**
     * Obtiene la lista de tipos de pagos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayrollPaymentTypes()
    {
        $records = [];

        $payments = PayrollPaymentType::with('financeBankAccount')->get()->toArray();


        array_push($records, [
            'id' => '',
            'text' => 'Seleccione...',
            'bank_account_id' => ''
        ]);

        foreach ($payments as $payment) {
            array_push($records, [
                'id' => $payment["id"],
                'text' => $payment["name"],
                'bank_account_id' => $payment["finance_bank_account"]["id"]
            ]);
        }

        return response()->json(['payment_types' => $records], 200);
    }

    /**
     * Obtiene la lista de cuentas bancarias del módulo de finanzas si esta presente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankAccounts()
    {
        $records = [];

        $accounts = (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? \Modules\Finance\Models\FinanceBankAccount::query()->all() : [];

        array_push($records, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);

        foreach ($accounts as $account) {
            array_push($records, [
                'id' => $account->id,
                'text' => $account->formated_ccc_number
            ]);
        }

        return response()->json(['bank_accounts' => $records], 200);
    }

    /**
     * Obtiene el reporte de cuentas presupuestarias del módulo de presupuesto si esta presente
     *
     * @param integer $payroll_id
     *
     * @return BinaryFileResponse
     */
    public function getBudgetAccountingReport($payroll_id)
    {
        $payrollStaffs = PayrollStaffPayroll::where('payroll_id', $payroll_id)->get()->toArray();
        $concept_types = array_keys($payrollStaffs[0]['concept_type']);
        $account_array = [];
        $assig_sum_total = 0.0;
        $deduc_sum_total = 0.0;

        $this->number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
        $this->round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
        $nameDecimalFunction = $this->round->p_value == 'false' ? 'currency_format' : 'round';

        $payment_period = [$payrollStaffs[0]["payroll"]["payroll_payment_period"]["start_date"], $payrollStaffs[0]["payroll"]["payroll_payment_period"]["end_date"]];

        foreach ($payrollStaffs as $payrollStaff) {
            foreach ($concept_types as $concept_type) {
                foreach ($payrollStaff['concept_type'][$concept_type] as $concept) {
                    if (
                        isset(
                            $concept['budget_account_code']
                        ) && ($concept['sign'] === '+' || $concept['sign'] === 'NA')
                    ) {
                        if (
                            $concept['budget_account_code']
                            && !key_exists($concept['budget_account_code'], $account_array)
                        ) {
                            $account_array[$concept['budget_account_code']] = [
                                $nameDecimalFunction(
                                    $concept['value'],
                                    $this->number_decimals->p_value
                                ),
                                $concept['budget_account_denomination'], 'budget'
                            ];
                        } elseif (
                            key_exists($concept['budget_account_code'], $account_array)
                        ) {
                            $account_array[$concept['budget_account_code']][0]
                                += $nameDecimalFunction(
                                    $concept['value'],
                                    $this->number_decimals->p_value
                                );
                        }
                    }
                }
            }
        }

        ksort($account_array);

        foreach ($account_array as $key => $value) {
            if ($value[2] == 'budget') {
                $assig_sum_total += $nameDecimalFunction($value[0], $this->number_decimals->p_value);
            }
        }


        $pdf = new ReportRepository();

        $institution = Institution::find(1);
        $payroll = Payroll::find($payroll_id);
        $paymentType = $payroll->payrollPaymentPeriod->payrollPaymentType->name;
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', auth()->user()->id)->first();

        $pdf->setConfig(['institution' => $institution, 'orientation' => 'P', 'urlVerify' => url('/login')]);
        $pdf->setHeader(
            'Reporte Presupuestario',
            'Periodo ' . date(
                'd-m-Y',
                strtotime($payment_period[0])
            ) . ' / ' . date(
                'd-m-Y',
                strtotime($payment_period[1])
            ),
            true,
            false,
            '',
            'C',
            'C'
        );
        $pdf->setFooter();
        $pdf->setBody('payroll::pdf.payroll-budget-accounting-report', true, [
            'pdf' => $pdf,
            'payment_type' => $paymentType,
            'records' => $account_array,
            'total_array' => [$assig_sum_total, $deduc_sum_total],
            'institution' => $institution,
            'currencySymbol' => $currency['symbol'],
            'fiscal_year' => $fiscal_year['year'],
            "report_date" => \Carbon\Carbon::today()->format('d-m-Y'),
            'profile' => $profile,
        ]);

        return Excel::download(new PayrollPdfExport([]), now() . date('d-m-Y') . 'Reporte_Presupuestario.pdf');
    }
}
