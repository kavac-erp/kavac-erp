<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use DateTime;
use Carbon\Carbon;
use App\Models\Parameter;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Date;
use App\Repositories\ReportRepository;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\ExchangeRate;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingEntryAccount;
use Modules\Accounting\Models\AccountingEntryCategory;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;

/**
 * @class AccountingReportPdfPatrimonialMovementController
 * @brief Controlador para la generación del reporte de Movimiento del Patromonio
 *
 * Clase que gestiona el reporte de movimiento del patrimonio
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingPatrimonialMovementController extends Controller
{
    /**
     * Salto de página
     *
     * @var mixed $PageBreakTrigger
     */
    protected $PageBreakTrigger;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:accounting.report.patrimonialmovement', [
            'only' => ['getAccAccount', 'calculateBeginningBalance', 'pdf','pdfVue']
        ]);
    }

    /**
     * Información del saldo inicial de cuentas patrimoniales
     *
     * @var float|null $beginningBalance
     */
    protected $beginningBalance = null;

    /**
     * Información de cuentas patrimoniales
     *
     * @var array $accountRecords
     */
    protected $accountRecords;

    /**
     * Fecha del rango inicial de búsqueda
     *
     * @var string $initDate
     */
    protected $initDate;

    /**
     * Fecha del rango final de búsqueda
     *
     * @var string $endDate
     */
    protected $endDate;

    /**
     * Lista de conversiones validas
     *
     * @var array $convertions
     */
    protected $convertions = [];

    /**
     * Moneda en la que se expresara el reporte
     *
     * @var Currency $currency
     */
    protected $currency;

    /**
     * Establece los registros
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param  array $records
     *
     * @return void
     */
    public function setRecords($records)
    {
        $this->accountRecords = $records;
    }

    /**
     * Obtiene los registros
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return array
     */
    public function getRecords()
    {
        return $this->accountRecords;
    }

    /**
     * Establece los registros del balance inicial
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param  integer $index   Índice del arreglo
     * @param  array   $records Listado de registros
     *
     * @return array
     */
    public function setBeginningBalanceRecord($index, $balance)
    {
        return $this->accountRecords[$index]['beginningBalance'] = $balance;
    }

    /**
     * Establece el balance inicial
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param  integer|string   $key   Índice del arreglo
     * @param  string           $value Valor del registro
     *
     * @return void
     */
    public function setBeginningBalance($key, $value)
    {
        if (is_null($this->beginningBalance)) {
            $this->beginningBalance = [];
        }
        $this->beginningBalance[$key] = $value;
    }

    /**
     * Obtiene los datos del balance inicial
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return array
     */
    public function getBeginningBalance()
    {
        return $this->beginningBalance;
    }

    /**
     * Establece la fecha inicial
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param  Date|string   $date   Fecha inicial
     *
     * @return void
     */
    public function setInitDate($date)
    {
        $this->initDate = $date;
    }

    /**
     * Obtiene la fecha inicial
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return Date|string|null
     */
    public function getInitDate()
    {
        return $this->initDate;
    }

    /**
     * Establece la fecha final
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param  Date|string   $date   Fecha inicial
     *
     * @return void
     */
    public function setEndDate($date)
    {
        $this->endDate = $date;
    }

    /**
     * Obtiene la fecha inicial
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return Date|string|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Obtiene las converciones de cuentas
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return array
     */
    public function getConvertions()
    {
        return $this->convertions;
    }

    /**
     * Establece las conversiones de cuentas
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param  array   $convertions   Arreglo con las conversiones de cuentas
     *
     * @return void
     */
    public function setConvertions($convertions)
    {
        $this->convertions = $convertions;
    }

    /**
     * Obtiene el identificador de la moneda
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return string|integer
     */
    public function getCurrencyId()
    {
        return $this->currency['id'];
    }

    /**
     * Obtiene los datos de la moneda
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Establece la moneda del registro
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Currency $currency
     *
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Cálcula la suma de los saldos de la cuenta en los asientos consatbles aprobados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $beginningBalance saldo inicial en caso de tener
     * @param  array $entryAccounts      arreglo de tipo Modules\Accounting\Models\AccountingEntryAccount
     *
     * @return float    suma total
     */
    public function calculateSum($beginningBalance, $entryAccounts)
    {
        $res = 0;
        foreach ($entryAccounts as $entryAccount) {
            if ($entryAccount['entries']) {
                if (!array_key_exists($this->getCurrencyId(), $this->getConvertions())) {
                    $this->setConvertions($this->calculateExchangeRates(
                        $this->getConvertions(),
                        $entryAccount['entries'],
                        $this->getCurrencyId()
                    ));
                }
                $res += $this->calculateOperation(
                    $this->getConvertions(),
                    $entryAccount['entries']['currency']['id'],
                    (floatval($entryAccount['debit']) - floatval($entryAccount['assets'])),
                    $entryAccount['entries']['from_date'],
                    ($entryAccount['entries']['currency']['id'] == $this->getCurrencyId()) ?? false
                );
            }
        }

        return $res;
    }

    /**
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author fjescala <fjescala@gmail.com>
     *
     * @param  array   $convertions     lista de tipos cambios para la moneda
     * @param  integer $ini             identificador del tipo de cuenta
     * @param  integer|float $debit           valor de la cuenta debe
     * @param  integer|float $assets          Valor de la cuenta en el haber
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
     * Realiza el calculo de las cuentas de acuerdo a como suman
     *
     * @author fjescala <fjescala@gmail.com>
     *
     * @param  array   $convertions     lista de tipos cambios para la moneda
     * @param  integer $ini             identificador del tipo de cuenta
     * @param  integer|float $debit           valor de la cuenta debe
     * @param  integer|float $assets          Valor de la cuenta en el haber
     *
     * @return float
     */
    public function getTotalResults($date, $formDate)
    {

        $balance = 0;

        $arrFive = [];
        $arrSix = [];
        $totResultFive = 0;
        $totResultSix = 0;
        $result_of_the_excersice = 0;
        $parentsArray = [];
        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $fiveAccountRecords = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->where('group', 5);
                });
        }])
            ->where('currency_id', $this->currency->id)
            ->where('institution_id', $institution->id)
            ->whereBetween('from_date', [$formDate, $date])
            ->get();

        foreach ($fiveAccountRecords as $fiveAccountRecord) {
            if ($fiveAccountRecord['accountingAccounts']) {
                foreach ($fiveAccountRecord['accountingAccounts'] as $account) {
                    $balance = $this->getRealBalaceCalculator(
                        $account['account']['code'][0],
                        $account['debit'],
                        $account['assets']
                    );
                    if (!array_key_exists($account['account']['code'], $arrFive)) {
                        $data = [
                            "id" => $account['account']['id'],
                            "code" => $account['account']['code'],
                            "denomination" => $account['account']['denomination'],
                            "balance" => $balance,
                        ];
                        $arrFive[$account['account']['code']] = $data;
                    } else {
                        $arrFive[$account['account']['code']]['balance'] += $balance;
                    }
                }
            }
        }

        foreach ($arrFive as $fiveAccount) {
            $totResultFive += $fiveAccount['balance'];
        }

        $sixAccountRecords = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->where('group', 6);
                });
        }])
            ->where('currency_id', $this->currency->id)
            ->where('institution_id', $institution->id)
            ->whereBetween('from_date', [$formDate, $date])
            ->get();

        foreach ($sixAccountRecords as $sixAccountRecord) {
            if ($sixAccountRecord['accountingAccounts']) {
                foreach ($sixAccountRecord['accountingAccounts'] as $account) {
                    $balance = $this->getRealBalaceCalculator(
                        $account['account']['code'][0],
                        $account['debit'],
                        $account['assets']
                    );
                    if (!array_key_exists($account['account']['code'], $arrSix)) {
                        $data = [
                            "id" => $account['account']['id'],
                            "code" => $account['account']['code'],
                            "denomination" => $account['account']['denomination'],
                            "balance" => $balance,
                        ];
                        $arrSix[$account['account']['code']] = $data;
                    } else {
                        $arrSix[$account['account']['code']]['balance'] += $balance;
                    }
                }
            }
        }

        foreach ($arrSix as $sixAccount) {
            $totResultSix += $sixAccount['balance'];
        }

        $result_of_the_excersice = $totResultFive - $totResultSix;

        return $result_of_the_excersice;
    }

    /**
     * Realiza el calculo de las cuentas de acuerdo a como suman
     *
     * @author fjescala <fjescala@gmail.com>
     *
     * @param  array   $convertions     lista de tipos cambios para la moneda
     * @param  integer $ini             identificador del tipo de cuenta
     * @param  integer|float $debit     valor de la cuenta debe
     * @param  integer|float $assets    Valor de la cuenta en el haber
     *
     * @return float
     */
    public function getInitTotalResults($date, $formDate)
    {
        // determinamos el primero de enero del año a buscar
        // Obtén el año de la fecha
        $year = explode('-', $formDate)[0];

        // Crea una nueva instancia de Carbon para el 1 de enero del mismo año
        $firstDay = Carbon::create($year, 1, 1);

        $balance = 0;

        $arr = [];
        $result_of_the_excersice = 0;
        $parentsArray = [];
        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $beginningBalances = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->whereIn('group', [5, 6]);
                });
        }])
            ->where('currency_id', $this->currency->id)
            ->where('institution_id', $institution->id)
            ->where('from_date', '<', $firstDay)
            ->get();

        foreach ($beginningBalances as $beginningBalance) {
            if ($beginningBalance['accountingAccounts']) {
                foreach ($beginningBalance['accountingAccounts'] as $account) {
                    $balance = $this->getRealBalaceCalculator(
                        $account['account']['code'][0],
                        $account['debit'],
                        $account['assets']
                    );
                    if (!array_key_exists($account['account']['code'], $arr)) {
                        $data = [
                            "id" => $account['account']['id'],
                            "code" => $account['account']['code'],
                            "denomination" => $account['account']['denomination'],
                            "beginningBalance" => $balance,
                            "level" => 6,
                            "parent" => [],
                        ];
                        $arr[$account['account']['code']] = $data;
                    } else {
                        $arr[$account['account']['code']]['beginningBalance'] += $balance;
                    }
                }
            }
        }

        foreach ($arr as $key => $a) {
            if ($a['code'][0] == 6) {
                $result_of_the_excersice -= $a['beginningBalance'];
            } elseif ($a['code'][0] == 5) {
                $result_of_the_excersice += $a['beginningBalance'];
            }
        }

        return $result_of_the_excersice;
    }

    /**
     * Consulta y formatea las cuentas en un rango determinado y las almacena en variables en el controlador
     * variable en las que almacena ($accountRecords, $initDate, $endDate)
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param string $initDate variable con la fecha inicial que recibe formato 'YYYY-mm'
     * @param string $endDate variable con la fecha inicial que recibe formato 'YYYY-mm'
     * @param boolean $returnArray bandera con la que se indica si viene de la funcion calculateBeginningBalance
     *                                      y limita las operaciones que esta requiere
     * @param array $beginningBalance lista con los saldos uniciales de las cuentas que sean distintos de 0
     * @param boolean $allRecords Bandera que indica si se consultaran las cuentas con 0 operaciones
     *
     * @return array
     */
    public function getAccAccount($initDate, $endDate, $returnArray, $all, $beginningBalance = [])
    {
        $beginningBalance = (!$beginningBalance) ? [] : $beginningBalance;

        /* almacenara la consulta */
        $query = [];

        /* almacenaran las cuentas patrimoniales de manera unica en el rango dando */
        $records = [];

        /* auxiliar para guardar las cuentas ordenadas */
        $arrAux = [];

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        if ($all) {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }]);
        } else {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } elseif ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                        }
                    }
                }])
                ->whereHas(
                    'entryAccount.entries',
                    function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        } elseif ($is_admin) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                        }
                    }
                );
        }
        $query = $query->where('group', 3)
                        ->where('subgroup', 2)
                        ->orderBy('group', 'ASC')
                        ->orderBy('subgroup', 'ASC')
                        ->orderBy('item', 'ASC')
                        ->orderBy('generic', 'ASC')
                        ->orderBy('specific', 'ASC')
                        ->orderBy('subspecific', 'ASC')
                        ->orderBy('denomination', 'ASC')->get();

        $lenghtQuery = count($query);

        $resultOfTheExersiceActv = AccountingAccount::where('group', "3")
            ->where('subgroup', "2")
            ->where('item', "5")
            ->where('generic', "02")
            ->where('specific', "01")
            ->where('subspecific', "01")
            ->where('institutional', "001")
            ->where('active', true)->first();


        /* Ciclo los registros de cuentas relacionadas con asiento contables */
        foreach ($query as $account) {
            /* indica si la cuenta ya esta en el array */
            $add = true;

            /* tamaño del arreglo */
            $lengthRecords = count($records);

            /* Ciclo para verificar que la cuenta no se repita en el array */
            for ($i = 0; $i < $lengthRecords; $i++) {
                if ($records[$i]['id'] == $account->id) {
                    $add = false;
                    break;
                }
            }

            if ($lengthRecords == 0 || $add) {
                if (!$returnArray) {
                    $val = $this->calculateSum(
                        (array_key_exists($account->id, $beginningBalance) ? $beginningBalance[$account->id] : 0),
                        $account->entryAccount
                    );

                    $beg = (array_key_exists($account->id, $beginningBalance)
                                                 ? $beginningBalance[$account->id] : 0);
                    array_push($records, [
                        'id'               => $account->id,
                        'code'             => $account->getCodeAttribute(),
                        'denomination'     => $account->denomination,
                        'category'         => AccountingEntryCategory::find($account->entryAccount[0]->entries->accounting_entry_category_id)->acronym,
                        'beginningBalance' => $beg,
                        'total_balance'    => $beg + $val,
                    ]);
                } else {
                    array_push($records, [
                        'id' => $account->id,
                    ]);
                }
            }
        }

        $accountValue = Parameter::where('p_key', 'close_fiscal_year_account')->get('p_value')->first()?->p_value;

        $nameEDR = AccountingAccount::find($accountValue)->code;

        if ($resultOfTheExersiceActv) {
            $data = [
                "id" => $resultOfTheExersiceActv->id,
                "code" => $nameEDR,
                "denomination" => "RESULTADOS DEL EJERCICIO",
                "category" => "EDR",
                'beginningBalance' => $this->getInitTotalResults($endDate, $initDate),
                "total_balance" => $this->getTotalResults($endDate, $initDate),
            ];
            $records[$lenghtQuery] = $data;
        } else {
            $data = [
                "id" => 348,
                "code" => $nameEDR,
                "denomination" => "RESULTADOS DEL EJERCICIO",
                "category" => "EDR",
                'beginningBalance' => $this->getInitTotalResults($endDate, $initDate),
                "total_balance" => $this->getTotalResults($endDate, $initDate),
            ];
            $records[$lenghtQuery] = $data;
        }

        if (!$returnArray) {
            $this->setRecords($records);
            $this->setInitDate($initDate);
            $this->setEndDate($endDate);
        }
        return $records;
    }

    /**
     * Calcula los saldos iniciales de cada cuenta en el rango dado, y lo alamcena
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param string $date variable con la fecha inicial que recibe formato 'YYYY-mm'
     */
    public function calculateBeginningBalance($initDate, $endDate, $all)
    {
        /* almacena el registro de asiento contable mas antiguo */
        $entries = AccountingEntry::where('approved', true)->orderBy('from_date', 'ASC')->first();

        $accounts = $this->getAccAccount($initDate, $endDate, true, $all, []);

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* Ciclo en el que se calcula y almancena los saldos iniciales de cada cuenta */
        foreach ($accounts as $account) {
            $balance = 0;
            foreach (
                AccountingEntryAccount::with('entries', 'account')
                        ->where('accounting_account_id', $account['id'])
                        ->whereHas('entries', function ($query) use ($initDate, $institution_id, $is_admin) {
                            if ($institution_id) {
                                $query->where('from_date', '<', $initDate)->where('approved', true)
                                    ->where('institution_id', $institution_id);
                            } else {
                                if ($is_admin) {
                                    $query->where('from_date', '<', $initDate)->where('approved', true);
                                }
                            }
                        })->orderBy('updated_at', 'ASC')->get() as $record
            ) {
                $balance += (float)$record->debit - (float)$record->assets;
            }
            if ($balance != 0) {
                $this->setBeginningBalance($account['id'], $balance);
            }
        }
    }

    /**
     * Genera el reporte pdf solicitado por el usuario
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param Date|string $initDate Fecha inicial
     * @param Date|string $endDate  Fecha final
     * @param Currency    $currency Datos de la moneda
     * @param boolean     $all      Indica si se generan todos los datos en el reporte
     *
     * @return JsonResponse
     */
    public function pdfVue($initDate, $endDate, Currency $currency, $all = false)
    {

        if ($all != false) {
            $all = true;
        }

        /* fecha inicial de busqueda */
        $initDate = $initDate . '-01';

        /* último dia correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, explode('-', $endDate)[1] + 1, 1, explode('-', $endDate)[0]) - 1));

        /* fecha final de busqueda */
        $endDate = $endDate . '-' . $endDay;

        /* almacenara la consulta */
        $query = [];

        /* Ciclo los registros de cuentas relacionadas con asiento contables */
        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        if ($all) {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }]);
        } else {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } elseif ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                        }
                    }
                }])
                ->whereHas(
                    'entryAccount.entries',
                    function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        } elseif ($is_admin) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                        }
                    }
                );
        }
        $query = $query->orderBy('group', 'ASC')
                        ->orderBy('subgroup', 'ASC')
                        ->orderBy('item', 'ASC')
                        ->orderBy('generic', 'ASC')
                        ->orderBy('specific', 'ASC')
                        ->orderBy('subspecific', 'ASC')
                        ->orderBy('denomination', 'ASC')->get();

        /* Se recorre y evalua la relacion en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $this->getConvertions())
                        && $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
                        $this->setConvertions($this->calculateExchangeRates(
                            $this->getConvertions(),
                            $entryAccount['entries'],
                            $currency['id']
                        ));
                    }

                    foreach ($this->getConvertions() as $convertion) {
                        foreach ($convertion as $convert) {
                            if (
                                $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                $entryAccount['entries']['from_date'] <= $convert['end_at']
                            ) {
                                $inRange = true;
                            }
                        }
                    }

                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $this->getConvertions())
                        && $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                                        . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                        . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                        ', verificar tipos de cambio configurados. Para la fecha de ' .
                                        $entryAccount['entries']['from_date'],
                        ], 200);
                    } elseif (!$inRange) {
                        if ($entryAccount['entries']['currency']['id'] != $currency->id) {
                            return response()->json([
                                'result' => false,
                                'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                                            . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                            . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                            ', verificar tipos de cambio configurados. Para la fecha de ' .
                                            $entryAccount['entries']['from_date'],
                            ], 200);
                        }
                    }
                }
            }
        }

        /* Se guarda un registro cada vez que se genera un reporte, en caso de que ya exista se actualiza */
        $all         = ($all != false) ? 'true' : '';

        $url         = 'patrimonialMovement/' . $initDate . '/' . $endDate . '/' . $all;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* almacena el registro del reporte del dia si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
                                                                        $currentDate . ' 00:00:00',
                                                                        $currentDate . ' 23:59:59'
                                                                    ])
                                        ->where('report', 'Movimiento del Patromonio' . (($all) ? ' - todas las cuentas' :
                                                                ' - solo cuentas con operaciones'))
                                        ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'url'            => $url,
                    'currency_id'    => $currency['id'],
                    'institution_id' => $institution_id,
                    'report'         => 'Movimiento del Patromonio' . (($all) ? ' - todas las cuentas' :
                                                                          ' - solo cuentas con operaciones'),
                ]
            );
        } else {
            $report->url            = $url;
            $report->currency_id    = $currency['id'];
            $report->institution_id = $institution_id;
            $report->save();
        }

        $this->setCurrency($report->currency);

        $entriesOnAccountingGroups = $this->getTotalResults($endDate, $initDate);

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * Genera el reporte pdf firmado
     *
     * @author Oscar Josúe González <ojgonzalez@cenditel.gob.ve>
     *
     * @param Date|string $initDate Fecha inicial
     * @param Date|string $endDate  Fecha final
     * @param Currency    $currency Datos de la moneda
     * @param boolean     $all      Indica si se generan todos los datos en el reporte
     *
     * @return JsonResponse
     */
    public function pdfVueSign($initDate, $endDate, Currency $currency, $all = false)
    {
        if ($all != false) {
            $all = true;
        }

        /* fecha inicial de busqueda */
        $initDate = $initDate . '-01';

        /* último dia correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, explode('-', $endDate)[1] + 1, 1, explode('-', $endDate)[0]) - 1));

        /* fecha final de busqueda */
        $endDate = $endDate . '-' . $endDay;

        /* almacenara la consulta */
        $query = [];

        /* Ciclo los registros de cuentas relacionadas con asiento contables */
        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        if ($all) {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                            }
                        }
                    }
                }]);
        } else {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } elseif ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                        }
                    }
                }])
                ->whereHas(
                    'entryAccount.entries',
                    function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                        if ($institution_id) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        } elseif ($is_admin) {
                            $query->whereBetween('from_date', [$initDate,$endDate])->where('approved', true);
                        }
                    }
                );
        }
        $query = $query->orderBy('group', 'ASC')
                        ->orderBy('subgroup', 'ASC')
                        ->orderBy('item', 'ASC')
                        ->orderBy('generic', 'ASC')
                        ->orderBy('specific', 'ASC')
                        ->orderBy('subspecific', 'ASC')
                        ->orderBy('denomination', 'ASC')->get();

        /* Se recorre y evalua la relacion en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $this->getConvertions())
                        && $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
                        $this->setConvertions($this->calculateExchangeRates(
                            $this->getConvertions(),
                            $entryAccount['entries'],
                            $currency['id']
                        ));
                    }

                    foreach ($this->getConvertions() as $convertion) {
                        foreach ($convertion as $convert) {
                            if (
                                $entryAccount['entries']['from_date'] >= $convert['start_at'] &&
                                $entryAccount['entries']['from_date'] <= $convert['end_at']
                            ) {
                                $inRange = true;
                            }
                        }
                    }

                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $this->getConvertions())
                        && $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                                        . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                        . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                        ', verificar tipos de cambio configurados. Para la fecha de ' .
                                        $entryAccount['entries']['from_date'],
                        ], 200);
                    } elseif (!$inRange) {
                        if ($entryAccount['entries']['currency']['id'] != $currency->id) {
                            return response()->json([
                                'result' => false,
                                'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                                            . ' (' . $entryAccount['entries']['currency']['name'] . ')'
                                            . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                                            ', verificar tipos de cambio configurados. Para la fecha de ' .
                                            $entryAccount['entries']['from_date'],
                            ], 200);
                        }
                    }
                }
            }
        }

        /* Se guarda un registro cada vez que se genera un reporte, en caso de que ya exista se actualiza */
        $all         = ($all != false) ? 'true' : '';

        $url         = 'patrimonialMovementSign/' . $initDate . '/' . $endDate . '/' . $all;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* almacena el registro del reporte del dia si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
                                                                        $currentDate . ' 00:00:00',
                                                                        $currentDate . ' 23:59:59'
                                                                    ])
                                        ->where('report', 'Movimiento del Patromonio' . (($all) ? ' - todas las cuentas' :
                                                                ' - solo cuentas con operaciones'))
                                        ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'url'            => $url,
                    'currency_id'    => $currency['id'],
                    'institution_id' => $institution_id,
                    'report'         => 'Movimiento del Patromonio' . (($all) ? ' - todas las cuentas' :
                                                                          ' - solo cuentas con operaciones'),
                ]
            );
        } else {
            $report->url            = $url;
            $report->currency_id    = $currency['id'];
            $report->institution_id = $institution_id;
            $report->save();
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * Vista en la que se genera el reporte en pdf de movimiento del patrimonio
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su informacion
     *
     * @return \Illuminate\View\View|void
     */
    public function pdf($report)
    {
        $report = AccountingReportHistory::with('currency')->find($report);
        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }
        $this->setInitDate(explode('/', $report->url)[1]);
        $this->setEndDate(explode('/', $report->url)[2]);
        $all = explode('/', $report->url)[3];
        $this->setCurrency($report->currency);

        /* fecha inicial del rango */
        $initDate = $this->getInitDate();

        /* fecha final del rango */
        $endDate = $this->getEndDate();

        /** Cálcula el saldo inicial que tendra la cuenta*/
        $this->calculateBeginningBalance($initDate, $endDate, $all);

        /* información del saldo inicial (id => balance) de las cuentas patrimoniales */
        $beginningBalance = $this->getBeginningBalance();

        /* asociativo con la información base */
        $accountRecords = $this->getAccAccount($initDate, $endDate, false, $all, $beginningBalance);

        $initDate       = new DateTime($initDate);
        $endDate        = new DateTime($endDate);

        $initDate       = $initDate->format('d/m/Y');
        $endDate        = $endDate->format('d/m/Y');

        /* configuración general de la apliación*/
        $setting = Setting::all()->first();

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/patrimonialMovement/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Movimiento del Patromonio');
        $pdf->setFooter();
        $pdf->setBody('accounting::pdf.patrimonial_movement', true, [
            'pdf'              => $pdf,
            'records'          => $accountRecords,
            'initDate'         => $initDate,
            'endDate'          => $endDate,
            'currency'         => $this->getCurrency(),
            'beginningBalance' => $beginningBalance,
        ]);
    }

    /**
     * Vista en la que se genera el reporte en pdf de movimiento del patrimonio
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su informacion
     *
     * @return JsonResponse|View|void
     */
    public function pdfSign($report)
    {
        $report = AccountingReportHistory::with('currency')->find($report);
        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }
        $this->setInitDate(explode('/', $report->url)[1]);
        $this->setEndDate(explode('/', $report->url)[2]);
        $all = explode('/', $report->url)[3];
        $this->setCurrency($report->currency);

        /* fecha inicial del rango */
        $initDate = $this->getInitDate();

        /* fecha final del rango */
        $endDate = $this->getEndDate();

        /* Cálcula el saldo inicial que tendra la cuenta */
        $this->calculateBeginningBalance($initDate, $endDate, $all);

        /* información del saldo inicial (id => balance) de las cuentas patrimoniales */
        $beginningBalance = $this->getBeginningBalance();

        /* asociativo con la información base */
        $accountRecords = $this->getAccAccount($initDate, $endDate, false, $all, $beginningBalance);

        $initDate       = new DateTime($initDate);
        $endDate        = new DateTime($endDate);

        $initDate       = $initDate->format('d/m/Y');
        $endDate        = $endDate->format('d/m/Y');

        /* configuración general de la apliación */
        $setting = Setting::all()->first();

        /* base para generar el pdf */
        $pdf = new ReportRepositorySign();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/patrimonialMovement/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Movimiento del Patromonio');
        $pdf->setFooter();
        $sign = $pdf->setBody('accounting::pdf.patrimonial_movement', true, [
            'pdf'              => $pdf,
            'records'          => $accountRecords,
            'initDate'         => $initDate,
            'endDate'          => $endDate,
            'currency'         => $this->getCurrency(),
            'beginningBalance' => $beginningBalance,
        ]);
        if ($sign['status'] == 'true') {
            return response()->download($sign['file'], $sign['filename'], [], 'inline');
        } else {
            return response()->json(['result' => $sign['status'], 'message' => $sign['message']], 200);
        }
    }

    /**
     * Realiza la conversion de saldo
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  array   $convertions   lista de tipos cambios para la moneda
     * @param  integer $entry_id      identificador del asiento
     * @param  float   $value         saldo del asiento
     * @param  float   $date          fecha del asiento
     * @param  boolean $equalCurrency bandera que indica si el tipo de moneda en el que esta el asiento es las misma
     *                                que la que se desea expresar
     *
     * @return float                  resultdado de la operación
     */
    public function calculateOperation($convertions, $currency_id, $value, $date, $equalCurrency)
    {
        if ($equalCurrency) {
            return $value;
        }

        if ($currency_id && array_key_exists($currency_id, $convertions) && $convertions[$currency_id]) {
            foreach ($convertions[$currency_id] as $convertion) {
                if ($date >= $convertion['start_at'] && $date <= $convertion['end_at']) {
                    if ($convertion['operator'] == 'to') {
                        return ($value * $convertion['amount']);
                    } else {
                        return ($value / $convertion['amount']);
                    }
                }
            }
        }
        return -1;
    }

    /**
     * Encuentra los tipos de cambio
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  array           $convertions lista de conversiones
     * @param  AccountingEntry $entry       asiento contable
     * @param  integer         $currency_id identificador de la moneda a la cual se realizara la conversion
     *
     * @return array                        [lista de conversiones actualizada]
     */
    public function calculateExchangeRates($convertions, $entry, $currency_id)
    {
        $exchangeRate = ExchangeRate::where('active', true)
                            ->whereIn('to_currency_id', [$entry['currency']['id'], $currency_id])
                            ->whereIn('from_currency_id', [$entry['currency']['id'], $currency_id])
                             ->orderBy('end_at', 'DESC')->get();
        if (count($exchangeRate) != 0) {
            if (!array_key_exists($entry['currency']['id'], $convertions)) {
                $convertions[$entry['currency']['id']] = [];
                foreach ($exchangeRate as $recordExchangeRate) {
                    array_push(
                        $convertions[$entry['currency']['id']],
                        [
                            'amount'   => $recordExchangeRate->amount,
                            'operator' => ($currency_id == $recordExchangeRate->from_currency_id) ? 'from' : 'to',
                            'start_at' => $recordExchangeRate->start_at,
                            'end_at'   => $recordExchangeRate->end_at
                        ]
                    );
                }
            }
        }
        return $convertions;
    }

    /**
     * Devolver PageBreakTrigger
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }
}
