<?php

namespace Modules\Finance\Http\Controllers;

use Carbon\Carbon;
use App\Models\Institution;
use App\Models\DocumentStatus;
use App\Models\Setting;
use App\Models\CodeSetting;
use App\Repositories\ReportRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingEntryAccount;
use Modules\Finance\Models\FinanceBankingMovement;
use Modules\Finance\Models\FinanceSettingBankReconciliationFiles;
use Modules\Finance\Models\FinanceConciliation;
use Modules\Finance\Models\FinanceConciliationBankMovement;
use Modules\Finance\Models\FinanceBankAccount;
use Modules\Accounting\Models\Profile;

/**
 * @class FinanceConciliationController
 *
 * @brief Gestión de Finanzas > Banco > Conciliación.
 *
 * Clase que gestiona lo referente a Conciliaciones bancarias.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceConciliationController extends Controller
{
    use ValidatesRequests;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        $this->messages = [
            'finance_bank_account_id.required' => 'El campo nro. de cuenta es
                obligatorio.',
            'start_date.required' => 'El campo fecha inicial es obligatorio.',
            'end_date.required' => 'El campo fecha final es obligatorio.',
            'institution_id.required' => 'El campo institución es obligatorio.',
            'currency_id.required' => 'El campo tipo de moneda es obligatorio.',
            'bank_balance.required' => 'El campo saldo en banco obtenido del
                archivo es obligatorio.'
        ];

        // Establece permisos de acceso para cada método del controlador
        // $this->middleware('permission:finance.settingbankreconciliationfiles.index', ['only' => 'index', 'vueList']);
    }

    /**
     * Muestra la plantilla del módulo Finanzas > Banco > Conciliación.
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('finance::conciliation.index');
    }

    /**
     * Retorna listado de conciliaciones para tabla
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json(
            [
                'records' => FinanceConciliation::with('financeBankAccount.bank', 'currency')->get()
            ],
            200
        );
    }

    /**
     * Obtiene un listado de operacion de cuentas en
     * asientos pendientes por conciliar
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer  $institution_id id de la institución
     * @param  integer  $currency_id id del tipo de moneda
     * @param  integer  $account_id id de la cuenta bancaria
     * @param  string  $startDate fecha inicial de la consulta
     * @param  string  $endDate fecha final de la consulta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListMovementsByAccount(
        $institution_id,
        $currency_id,
        $account_id,
        $startDate,
        $endDate
    ) {
        $movement_accounting_account_id = FinanceBankAccount::find($account_id)
            ->accounting_account_id;

        $movements = AccountingEntryAccount::doesntHave(
            'financeConciliationBankMovement'
        )->with('entries.documentStatus')
            ->whereHas(
                'entries',
                function ($query) use (
                    $institution_id,
                    $currency_id,
                    $startDate,
                    $endDate
                ) {
                    $query->where('institution_id', $institution_id)
                        ->where('currency_id', $currency_id)
                        ->whereBetween('from_date', [$startDate, $endDate]);
                }
            )->whereHas(
                'entries.documentStatus',
                function ($query) {
                    $query->where('action', 'AP');
                }
            )
        ->where('accounting_account_id', $movement_accounting_account_id)
        ->get();

        return response()->json(
            [
                'records' => $movements,
            ],
            200
        );
    }

    /**
     * Muestra la plantilla del módulo Finanzas > Banco > Conciliación > create.
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::conciliation.form');
    }

    /**
     * Realiza el calculo de las cuentas de acuerdo a como suman
     *
     * @author fjescala <fjescala@gmail.com>
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  Carbon $formDate fecha inicial de la consulta
     * @param  Carbon $endDate fecha final de la consulta
     * @param  integer $currency_id id del tipo de moneda
     * @param  integer $accounting_account_id id de la cuenta contable
     *
     * @return float
     */
    public function getLastTotalResults(
        Carbon $formDate,
        Carbon $endDate,
        int $currency_id,
        int $accounting_account_id
    ): float {
        // determinamos el primero de enero del año a buscar
        // Obtén el año de la fecha
        $ano = $formDate->year;

        // Crea una nueva instancia de Carbon para el 1 de enero del mismo año
        $primerDeEnero = Carbon::create($ano, 1, 1);
        $balance = 0;

        $arr = [];
        $result_of_the_excersice = 0;
        $parentsArray = [];
        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where(
                'user_id',
                auth()->user()->id
            )->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $beginnBalances = AccountingEntry::query()
            ->with(
                [
                    'accountingAccounts' => function ($query) use (
                        $accounting_account_id
                    ) {
                        $query->with('account')
                            ->whereHas(
                                'account',
                                function ($query) use ($accounting_account_id) {
                                    $query->where('id', $accounting_account_id);
                                }
                            );
                    }
                ]
            )
            ->where('currency_id', $currency_id)
            ->where('institution_id', $institution->id)
            ->whereBetween('from_date', [$primerDeEnero, $endDate])
            ->get();

        foreach ($beginnBalances as $beginnbalance) {
            if ($beginnbalance['accountingAccounts']) {
                foreach ($beginnbalance['accountingAccounts'] as $account) {
                    $balance = $this->getRealBalaceCalculator(
                        $account['account']['code'][0],
                        $account['debit'],
                        $account['assets']
                    );
                    if (array_key_exists($account['account']['code'], $arr)) {
                        $arr[
                            $account['account']['code']
                        ]['lastMonthBalance'] += $balance;
                    } else {
                        $acc = [
                        'denomination' => $account['account']['denomination'],
                        'code' => $account['account']["code"],
                        'lastMonthBalance' => $balance,
                        ];
                        $arr[$account['account']['code']] = $acc;
                    }
                }
            }
        }
        foreach ($arr as $key => $a) {
            $result_of_the_excersice = $a['lastMonthBalance'];
        }

        return $result_of_the_excersice ?? 0.00;
    }

    /**
     * Realiza el calculo de las cuentas de acuerdo a como suman
     *
     * @author fjescala <fjescala@gmail.com>
     *
     * @param  integer $ini     Identificador del tipo de cuenta
     * @param  integer $debit   Valor de la cuenta debe
     * @param  integer $assets  Valor de la cuenta en el haber
     *
     * @return float
     */

    public function getRealBalaceCalculator($ini, $debit, $assets)
    {
        $balance = 0;
        switch ($ini) {
            case 1:
                $balance = $debit - $assets;
                break;
            case 2:
                $balance = $assets - $debit;
                break;
            case 3:
                $balance = $assets - $debit;
                break;
            case 4:
                $balance = $debit - $assets;
                break;
            case 5:
                $balance = $assets - $debit;
                break;
            case 6:
                $balance = $debit - $assets;
                break;
        }

        return $balance;
    }

    /**
     * Guarda la informacion de la conciliación
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'finance_bank_account_id' => ['required'],
                'start_date' => ['required'],
                'end_date' => ['required'],
                'institution_id' => ['required'],
                'currency_id' => ['required'],
                'bank_balance' => ['required'],
            ],
            $this->messages
        );

        $docStatus = DocumentStatus::where('action', 'AP')->first();

        $data = $request->all();

        $financeConciliationApproveInMonth = FinanceConciliation::where(
            'start_date',
            '>=',
            $data['start_date']
        )->where('end_date', '<=', $data['end_date'])
            ->where('document_status_id', $docStatus->id)->first();

        if (!empty($financeConciliationApproveInMonth)) {
            return response()->json(
                [
                    'errors' => [
                        'start_date' => [
                            'Se encontraron registros de conciliaciones
                                aprobadas para este mes'
                        ]
                    ]
                ],
                422
            );
        }

        $codeSetting = CodeSetting::where('table', 'finance_conciliations')->first();

        if (is_null($codeSetting)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente el formato
                        para el código a generar',
                ]
            );
            return response()->json(
                [
                    'result' => false,
                    'redirect' => route('finance.setting.index')
                ],
                200
            );
        }

        $codeConciliation = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? date('y') : date('Y'),
            $codeSetting->model,
            $codeSetting->field
        );

        /* Estado inicial del registro establecido a 'En Proceso' = 'Pendiente' */
        $documentStatusPR = DocumentStatus::where('action', 'PR')->first();

        $fromDate = Carbon::createFromFormat('Y-m-d', $data['start_date']);
        $endDate = Carbon::createFromFormat('Y-m-d', $data['end_date']);

        $accounting_account_id = FinanceBankAccount::find(
            $data['finance_bank_account_id']
        )->accounting_account_id;


        $conciliation = FinanceConciliation::create(
            [
                'code' => $codeConciliation,
                'finance_bank_account_id' => $data['finance_bank_account_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'institution_id' => $data['institution_id'],
                'currency_id' => $data['currency_id'],
                'bank_balance' => $data['bank_balance'],
                'system_balance' => $this->getLastTotalResults(
                    $fromDate,
                    $fromDate,
                    $data['currency_id'],
                    $accounting_account_id
                ),
                'document_status_id' => $documentStatusPR->id,
            ]
        );

        foreach ($data['movementToConsolidate'] as $conc) {
            FinanceConciliationBankMovement::create(
                [
                    'finance_conciliation_id' => $conciliation->id,
                    'accounting_entry_account_id' => $conc['movement']['id'],
                    'concept' => $conc['consolidation']['concept'],
                    'debit' => $conc['consolidation']['debit'],
                    'assets' => $conc['consolidation']['assets'],
                    'current_balance' => $conc['consolidation']['current_balance'],
                ]
            );
        }

        return response()->json(
            [
                'result' => true,
                'redirect' => route('finance.conciliation.index')
            ],
            200
        );
    }

    /**
     * Retorna la informacion de una conciliación
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer $id ID de la conciliación bancaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $financeConciliation = FinanceConciliation::with(
            'currency',
            'institution',
            'financeBankAccount',
            'financeConciliationBankMovements.accountingEntryAccount.entries'
        )->find($id);

        return response()->json(['records' => $financeConciliation], 200);
    }

    /**
     * Muestra la plantilla del módulo Finanzas > Banco > Conciliación > create.
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer $id ID de la conciliación bancaria
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $financeConciliation = FinanceConciliation::with(
            'currency',
            'institution',
            'financeBankAccount',
            'financeConciliationBankMovements.accountingEntryAccount.entries'
        )->find($id);

        return view(
            'finance::conciliation.form',
            compact('financeConciliation')
        );
    }

    /**
     * Actualiza la informacion de la conciliacion
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     * @param  integer $id ID de la conciliación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'finance_bank_account_id' => ['required'],
                'start_date' => ['required'],
                'end_date' => ['required'],
                'institution_id' => ['required'],
                'currency_id' => ['required'],
                'bank_balance' => ['required'],
            ],
            $this->messages
        );

        $data = $request->all();

        $conciliation = FinanceConciliation::find($id)->update(
            [
                'finance_bank_account_id' => $data['finance_bank_account_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'institution_id' => $data['institution_id'],
                'currency_id' => $data['currency_id'],
                'bank_balance' => $data['bank_balance'],
            ]
        );

        $FinConbankMov = FinanceConciliationBankMovement::where(
            'finance_conciliation_id',
            $id
        )->get();

        foreach ($FinConbankMov as $rec) {
            $rec->delete();
        }

        foreach ($data['movementToConsolidate'] as $conc) {
            FinanceConciliationBankMovement::create(
                [
                    'finance_conciliation_id' => $id,
                    'accounting_entry_account_id' => $conc['movement']['id'],
                    'concept' => $conc['consolidation']['concept'],
                    'debit'   => $conc['consolidation']['debit'],
                    'assets'  => $conc['consolidation']['assets'],
                    'current_balance' => $conc['consolidation']['current_balance'],
                ]
            );
        }

        return response()->json(
            [
                'result' => true,
                'redirect' => route('finance.conciliation.index')
            ],
            200
        );
    }

    /**
     * Actualiza el estado de una conciliacion
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer $id ID de la conciliación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id)
    {
        $documentStatus = DocumentStatus::where('action', 'AP')->first();

        $conciliation = FinanceConciliation::find($id);

        $conciliation->document_status_id = $documentStatus->id;

        $conciliation->save();

        return response()->json(
            [
                'result' => true,
                'redirect' => route('finance.conciliation.index')
            ],
            200
        );
    }

    /**
     * Elimina una conciliación
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param  integer $id ID de la conciliación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $documentStatus = DocumentStatus::where('action', 'PR')->first();

        $conciliation = FinanceConciliation::find($id);

        if ($conciliation->document_status_id != $documentStatus->id) {
            return response()->json(
                [
                    'error' => true,
                    'message' => 'Solo se permiter eliminar una conciliación
                        mientras no este aprobada.'
                ],
                200
            );
        }

        $conciliationMovements = FinanceConciliationBankMovement::where(
            'finance_conciliation_id',
            $id
        )->get();

        foreach ($conciliationMovements as $cm) {
            $cm->delete();
        }

        $conciliation->delete();

        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param integer $id id de la conciliación bancaria
     *
     * @return \Illuminate\View\View|void
     */
    public function pdf($id)
    {
        // Validar acceso para el registro
        $is_admin = auth()->user()->isAdmin();

        $user_profile = Profile::with('institution')->where(
            'user_id',
            auth()->user()->id
        )->first();

        if (!$is_admin && $user_profile && $user_profile['institution']) {
            $conciliation = FinanceConciliation::with(
                'financeBankAccount',
                'financeConciliationBankMovements.accountingEntryAccount.entries'
            )->where(
                'institution_id',
                $user_profile['institution']['id']
            )->find($id);
        } else {
            $conciliation = FinanceConciliation::with(
                'financeBankAccount',
                'financeConciliationBankMovements.accountingEntryAccount.entries'
            )->find($id);
        }

        if (!auth()->user()->isAdmin()) {
            if ($conciliation && $conciliation->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        /* configuración general de la apliación */
        $setting = Setting::all()->first();

        $OnlyOneEntry   = true;

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        if (!$is_admin && $user_profile && $user_profile['institution']) {
            $institution = Institution::find($user_profile['institution']['id']);
        } else {
            $institution = get_institution();
        }

        $fromDate = Carbon::createFromFormat('Y-m-d', $conciliation->start_date);

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify' => url(' entries/pdf/' . $id)
            ]
        );

        $pdf->setHeader(
            'Reporte de Conciliación Bancaria',
            'Reporte de Conciliación Bancaria'
        );

        $pdf->setFooter();

        $pdf->setBody(
            'finance::pdf.conciliation_report',
            true,
            [
                'pdf'      => $pdf,
                'conciliation'    => $conciliation,
                'currency' => $conciliation->currency,
            ]
        );
    }

    /**
     * Método que retorna el delimitador usado un archiv .csv
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param string $filePath      Ruta del archivo .csv
     * @param array $delimiters     Delimitadores del archivo .csv
     *
     * @return string
     */
    public function guessDelimiter(
        string $filePath,
        array $delimiters = [';', ',', "\t"]
    ): string {
        $file = fopen($filePath, 'r');

        $firstLine = fgets($file);

        fclose($file);

        $ns = [];
        foreach ($delimiters as $delimiter) {
            $ns[$delimiter] = count(explode($delimiter, $firstLine));
        }

        return collect($ns)->sort()->reverse()->keys()->first();
    }


    /**
     * Método que obtiene el separador usado en el formato de monto
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param array $csvData     Contenido del archivo .csv
     * @param array $separators  Separadores de decimales
     *
     * @return string
     */
    public function guessDecimalSeparator(
        array $csvData,
        array $separators = ['.', ',']
    ) {
        $ns = [];
        foreach ($separators as $separator) {
            for ($i = 1; $i < count($csvData); $i++) {
                $ns[$separator] = count(explode($separator, $csvData[$i][5]));
            }
        }
        $separator = collect($ns)->sort()->reverse()->keys()->first();
        return $separator;
    }

    /**
     * Método que obtiene el array de validación para el archivo de configuración
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param $bank_id Id del banco asociado a la cuenta bancaria
     *
     * @return array
     */
    public function getFileValidationArray($bank_id)
    {
        $fileStructure = FinanceSettingBankReconciliationFiles::where(
            'bank_id',
            $bank_id
        )->first()?->toArray();

        return $fileStructure;
    }

    /**
     * Método que consigue el delimitador usado en el archivo csv/.txt
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param $delimiter Delimitador usado por el archivo csv/.txt
     * @param $fileDelimiters Delimitadores disponibles
     *
     * @return string
     */
    public function getFileConfigurationDelimiter(
        $delimiter,
        $fileDelimiters = [
            ';' => 'Punto y Coma',
            "\t" => 'Tabulador',
            ',' => 'Coma'
        ]
    ) {
        return $fileDelimiters[$delimiter];
    }

    /**
     * Método que consigue el separador de decimales usado en el archivo csv/.txt
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param $separator Separador usado por el archivo csv/.txt
     * @param $fileDelimiters Delimitadores disponibles
     *
     * @return string
     */
    public function getFileConfigurationDecimalSeparator(
        $separator,
        $fileDelimiters = [
            ',' => 'Coma',
            "." => 'Punto'
        ]
    ) {
        return $fileDelimiters[$separator];
    }

    /**
     * Método que valida la estructura del archivo usado para realizar la conciliación bancaria
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param \SplFileInfo $file Archivo csv/.txt
     * @param $bank_id Id del banco
     *
     * @throws \Exception Lanzar error de validación
     *
     * @return array|object
     */
    public function validateFileStructure($file, $bank_id)
    {
        // Encontrar el archivo de configuración para el banco asociado a la cuenta bancaria
        $fileStructure = $this->getFileValidationArray($bank_id);

        // Si no existe, lanzar error de validación
        throw_if(
            is_null($fileStructure),
            'El banco asociado a la cuenta bancaria no tiene una
                configuración para el archivo ' .
            'de conciliación bancaria. Por favor registre una configuración.'
        );

        // Leer archivo csv/.txt
        $filePath = $file->getRealPath();

        // Delimitador del archivo csv/.txt
        $delimiter = $this->guessDelimiter($filePath);
        // Obtener el string correspodiente a los delimitadores ';' | ',' | '\t'
        $translatedDelimiter = $this->getFileConfigurationDelimiter($delimiter);

        // Si el delimitador del archivo csv/.txt no coincide con el del archivo de configuración, lanzar error de validación
        throw_if(
            $translatedDelimiter != $fileStructure['separated_by'],
            'El archivo no se encuentra separado por el separador
                indicado en la configuración'
        );


        $csvData = [];


        if (($open = fopen($filePath, "r")) !== false) {
            if ($fileStructure["read_start_line"] == true) {
                fgetcsv($open, 700, $delimiter);
            }

            while (($data = fgetcsv($open, 700, $delimiter)) !== false) {
                $csvData[] = is_array($data) ? trim($data[0]) : trim($data);
            }

            if ($fileStructure["read_end_line"] == false) {
                array_pop($csvData);
            }

            fclose($open);
        }

        $decimalSeparator = $this->guessDecimalSeparator($csvData);
        $translatedDecimalSeparator = $this->getFileConfigurationDecimalSeparator(
            $decimalSeparator
        );

        throw_if(
            $translatedDecimalSeparator != $fileStructure['decimal_separator'],
            'El archivo no esta usando el separador de decimales
                indicado en la configuración'
        );
        return $csvData;
    }

    /**
     * Método que devuelve el id del banco al que pertenece una cuenta bancaria
     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param integer $account_id id de la cuenta bancaria
     *
     * @return integer $id id del banco al que pertenece una cuenta bancaria
     */
    public function getBankId($account_id)
    {
        $id = DB::table('finance_banks')
            ->join(
                'finance_bank_accounts',
                'finance_banks.id',
                '=',
                'finance_bank_accounts.finance_bank_id'
            )
            ->where('finance_bank_accounts.id', '=', $account_id)
            ->select('finance_banks.id')
            ->first()->id;

        return $id;
    }

    /**
     * Método que encuentra y retorna los movimientos de una cuenta
     * bancaria que cumplen con parámetros de busqueda
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankAccountConciliationInfo(Request $request)
    {
        $this->validate(
            $request,
            [
                'account_id' => ['required'],
                'month' => ['required'],
                'year' => ['required'],
                'file' => ['required', 'mimes:csv,txt'],
            ],
            [],
            [
                'account_id' => 'cuenta bancaria',
                'file' => 'archivo'
            ]
        );
        $bank_id = $this->getBankId($request->account_id);
        $bankMovements = [];

        if (!empty($request->files) && $request->hasFile('file')) {
            $structure = FinanceSettingBankReconciliationFiles::where(
                'bank_id',
                $bank_id
            )->first()?->toArray();

            try {
                $csvData = $this->validateFileStructure(
                    $request->file('file'),
                    $bank_id
                );

                foreach ($csvData as $key => $row) {
                    $row = preg_replace('/\s+/', ' ', $row);
                    $bankMovements[] = explode(' ', $row);
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                return response()->json(
                    [
                        'errors' => [
                            'file' => [
                                'Se encontraron errores en el formato del archivo
                                    suministrado con respecto a la configuración ' .
                                'indicada para el archivo.'
                            ]
                        ]
                    ],
                    422
                );
            }
        }

        $accountMovements = FinanceBankingMovement::where(
            'finance_bank_account_id',
            $request->account_id
        )
        ->whereYear('payment_date', $request->year)
        ->whereMonth('payment_date', $request->month)->get();

        return response()->json(
            [
                'result' => true,
                'movements' => $accountMovements,
                'bankMovements' => $bankMovements
            ],
            200
        );
    }

    /**
     * Obtiene los datos de la organización asociada al usuario autenticado o en
     * su defecto, la organización activa y por defecto.
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstitution()
    {
        if (isset(auth()->user()->profile)) {
            if (isset(auth()->user()->profile->institution_id)) {
                $institution = Institution::where(
                    [
                        'id' => auth()->user()->profile->institution_id
                    ]
                )->first();
            } else {
                $institution = Institution::where(
                    [
                        'active' => true,
                        'default' => true
                    ]
                )->first();
            }
        } else {
            $institution = Institution::where(
                [
                    'active' => true,
                    'default' => true
                ]
            )->first();
        }
        $inst = Institution::where(
            'id',
            $institution->id
        )->with(
            [
                'municipality' => function ($q) {
                    return $q->with(
                        [
                            'estate' => function ($qq) {
                                return $qq->with('country');
                            }
                        ]
                    );
                },
                'banner',
                'logo'
            ]
        )->first();

        return response()->json(['result' => true, 'institution' => $inst], 200);
    }
}
