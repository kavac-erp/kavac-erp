<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use Auth;
use DateTime;
use Dompdf\Renderer;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReportRepository;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\ExchangeRate;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingEntryAccount;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;
use Modules\Accounting\Exports\AccountingBalanceCheckUpSheetExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class AccountingCheckupBalanceController
 * @brief Controlador para la generación del reporte de Balance de Comprobación
 *
 * Clase que gestiona el reporte de balance de comprobación
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingCheckupBalanceController extends Controller
{
    /**
     * Información del saldo inicial de cuentas patrimoniales
     *
     * @var array|null $beginningBalance
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
     * Moneda en la que se expresará el reporte
     *
     * @var Currency $currency
     */
    protected $currency;

    /**
     * Define el salto de página
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
        $this->middleware('permission:accounting.report.checkupbalance', [
            'only' => ['getAccAccount', 'calculateBeginningBalance', 'pdf','pdfVue']
        ]);
    }

    /**
     * Establece los registros
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  array $records Registros de las cuentas contables
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
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return array  Registros de las cuentas contables
     */
    public function getRecords()
    {
        return $this->accountRecords;
    }

    /**
     * Establece los registros del balance inicial
     *
     * @param integer $index   Índice del arreglo de registros
     * @param float   $balance Saldo inicial
     *
     * @return array|float
     */
    public function setBeginningBalanceRecord($index, $balance)
    {
        return $this->accountRecords[$index]['beginningBalance'] = $balance;
    }

    /**
     * Establece el saldo inicial
     *
     * @param integer $key   Índice del arreglo de registros
     * @param float   $value Saldo inicial
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
     * Obtiene información del balance inicial
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
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
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param Date|string $date Fecha inicial
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
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Date|string
     */
    public function getInitDate()
    {
        return $this->initDate;
    }

    /**
     * Establece la fecha final
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return void
     */
    public function setEndDate($date)
    {
        $this->endDate = $date;
    }

    /**
     * Obtiene la fecha final
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Date|string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Obtiene las conversiones
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return array
     */
    public function getConvertions()
    {
        return $this->convertions;
    }

    /**
     * Establece las conversiones
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param array $convertions Listado de conversiones
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
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return integer
     */
    public function getCurrencyId()
    {
        return $this->currency['id'];
    }

    /**
     * Obtiene información de la moneda
     *
     * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Establece los datos de la moneda
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
     * Calcula la suma de los saldos de la cuenta en los asientos contables aprobados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $beginningBalance Saldo inicial en caso de tener
     * @param  AccountingEntryAccount $entryAccounts      Arreglo Asientos contables
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
     * Consulta y formatea las cuentas en un rango determinado y las almacena en variables en el controlador
     * variable en las que almacena ($accountRecords, $initDate, $endDate)
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param Date $initDate variable con la fecha inicial que recibe formato 'YYYY-mm'
     * @param Date $endDate variable con la fecha inicial que recibe formato 'YYYY-mm'
     * @param boolean $returnArray bandera con la que se indica si viene de la funcion calculateBeginningBalance
     *                             y limita las operaciones que esta requiere
     * @param boolean $allRecords Bandera que indica si se consultaran las cuentas con 0 operaciones
     * @param array|null $beginningBalance lista con los saldos iniciales de las cuentas que sean distintos de 0
     *
     * @return array Devuelve el listado de registros
     */
    public function getAccAccount($initDate, $endDate, $returnArray, $all, $beginningBalance = [])
    {
        $beginningBalance = (!$beginningBalance) ? [] : $beginningBalance;

        /* Almacenara la consulta */
        $query = [];

        /* Almacenaran las cuentas patrimoniales de manera única en el rango dando */
        $records = [];

        /* Auxiliar para guardar las cuentas ordenadas */
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
        $query = $query->orderBy('group', 'ASC')
                        ->orderBy('subgroup', 'ASC')
                        ->orderBy('item', 'ASC')
                        ->orderBy('generic', 'ASC')
                        ->orderBy('specific', 'ASC')
                        ->orderBy('subspecific', 'ASC')
                        ->orderBy('denomination', 'ASC')->get();
        /* Ciclo para los registros de cuentas relacionadas con asiento contables */
        foreach ($query as $account) {
            /* Indica si la cuenta ya esta en el array */
            $add = true;

            /* Tamaño del arreglo */
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
                        'beginningBalance' => $beg,
                        'sum_debit'        => ($val >= 0) ? $val : null,
                        'sum_assets'       => ($val < 0) ? $val : null,
                        'balance_debit'    => (floatval($beg) + $val >= 0) ? floatval($beg) + $val : null ,
                        'balance_assets'   => (floatval($beg) + $val < 0) ? floatval($beg) + $val : null ,
                    ]);
                } else {
                    array_push($records, [
                        'id' => $account->id,
                    ]);
                }
            }
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
     * @param Date $initDate variable con la fecha inicial que recibe formato 'YYYY-mm'
     * @param Date $endDate  Variable con la fecha final con formato 'YYYY-mm'
     * @param array|string $all     Lista de todos los registros
     *
     * @return void
     */
    public function calculateBeginningBalance($initDate, $endDate, $all)
    {
        /* Almacena el registro de asiento contable mas antiguo */
        $entries = AccountingEntry::where('approved', true)->orderBy('from_date', 'ASC')->first();

        $accounts = $this->getAccAccount($initDate, $endDate, true, $all, null);

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* Ciclo en el que se cálcula y almancena los saldos iniciales de cada cuenta */
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
     * Genera el reporte del balance de comprobación
     *
     * @param   Date|string     $initDate   Fecha inicial
     * @param   Date|string     $endDate    Fecha final
     * @param   Currency        $currency   Moneda del reporte
     * @param   boolean         $all        Indica si se van a mostrar todas las cuentas
     *
     * @return  JsonResponse
     */
    public function pdfVue($initDate, $endDate, Currency $currency, $all = false)
    {
        if ($all != false) {
            $all = true;
        }

        /* Fecha inicial de búsqueda */
        $initDate = $initDate . '-01';

        /* Último día correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, explode('-', $endDate)[1] + 1, 1, explode('-', $endDate)[0]) - 1));

        /* Fecha final de búsqueda */
        $endDate = $endDate . '-' . $endDay;

        /* Almacenara la consulta */
        $query = [];

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
        /* Se recorre y evalúa la relación en las conversiones necesarias a realizar */
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

        $dataInDate = $query->toArray();
        if ($dataInDate == []) {
            return response()->json(
                [
                    'result' => false,
                    'message' => 'No se ha encontrado ningún registro entre el rango de fechas establecido.',
                ],
                200
            );
        }

        /* Se guarda un registro cada vez que se genera un reporte, en caso de que ya exista se actualiza */
        $all         = ($all != false) ? 'true' : '';

        $url         = 'balanceCheckUp/' . $initDate . '/' . $endDate . '/' . $all;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Almacena el registro del reporte del día si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
                                                                        $currentDate . ' 00:00:00',
                                                                        $currentDate . ' 23:59:59'
                                                                    ])
                                        ->where('report', 'Balance de Comprobación' . (($all) ? ' - todas las cuentas' :
                                                                ' - solo cuentas con operaciones'))
                                        ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'url'            => $url,
                    'currency_id'    => $currency['id'],
                    'institution_id' => $institution_id,
                    'report'         => 'Balance de Comprobación' . (($all) ? ' - todas las cuentas' :
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
     * Genera el reporte firmado
     *
     * @param   Date|string    $initDate  Fecha inicial
     * @param   Date|string    $endDate   Fecha final
     * @param   Currency  $currency  Datos de la moneda
     * @param   boolean    $all       Establece si se generan todos los registros
     *
     * @return  JsonResponse
     */
    public function pdfVueSign($initDate, $endDate, Currency $currency, $all = false)
    {
        if ($all != false) {
            $all = true;
        }

        /* Fecha inicial de búsqueda */
        $initDate = $initDate . '-01';

        /* Último día correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, explode('-', $endDate)[1] + 1, 1, explode('-', $endDate)[0]) - 1));

        /* Fecha final de búsqueda */
        $endDate = $endDate . '-' . $endDay;

        /* Almacenara la consulta */
        $query = [];

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
        /* Se recorre y evalúa la relación en las conversiones necesarias a realizar */
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

        $url         = 'balanceCheckUpSign/' . $initDate . '/' . $endDate . '/' . $all;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Almacena el registro del reporte del dia si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
                                                                        $currentDate . ' 00:00:00',
                                                                        $currentDate . ' 23:59:59'
                                                                    ])
                                        ->where('report', 'Balance de Comprobación' . (($all) ? ' - todas las cuentas' :
                                                                ' - solo cuentas con operaciones'))
                                        ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'url'            => $url,
                    'currency_id'    => $currency['id'],
                    'institution_id' => $institution_id,
                    'report'         => 'Balance de Comprobación' . (($all) ? ' - todas las cuentas' :
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
     * Genera el reporte en hojade cálculo de balance general
     *
     * @param  integer $report id de reporte y su información
     *
     * return mixed
     */
    public function export($report)
    {
        return  $this->pdf($report, true);
    }

    /**
     * Vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     * @param boolean $xml Indica si el reporte se genera en xml
     *
     * @return Renderer|View|BinaryFileResponse|null|void
     */
    public function pdf($report, $xml = false)
    {
        $report = AccountingReportHistory::with('currency')->find($report);
        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
            $institution = Institution::find($user_profile['institution']['id']);
        } else {
            $institution = Institution::where('default', true)->first();
        }
        $this->setInitDate(explode('/', $report->url)[1]);
        $this->setEndDate(explode('/', $report->url)[2]);
        $all = explode('/', $report->url)[3];
        $this->setCurrency($report->currency);

        /* fecha inicial del rango */
        $initDate = $this->getInitDate();

        /* fecha final del rango */
        $endDate = $this->getEndDate();

        /* Cálcula el saldo inicial que tendra la cuenta*/
        $this->calculateBeginningBalance($initDate, $endDate, $all);

        /* Información del saldo inicial (id => balance) de las cuentas patrimoniales */
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
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */

        if ($xml) {
            return Excel::download(new AccountingBalanceCheckUpSheetExport([
                'pdf'              => $pdf,
                'records'          => $accountRecords,
                'initDate'         => $initDate,
                'endDate'          => $endDate,
                'currency'         => $this->getCurrency(),
                'beginningBalance' => $beginningBalance,
            ]), now()->format('d-m-Y') . '_Balance _Comprobación.xlsx');
        } else {
            $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/balanceCheckUp/' . $report->id)]);
            $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Balance de Comprobación');
            $pdf->setFooter();
            $pdf->setBody('accounting::pdf.checkup_balance', true, [
                'pdf'              => $pdf,
                'records'          => $accountRecords,
                'initDate'         => $initDate,
                'endDate'          => $endDate,
                'currency'         => $this->getCurrency(),
                'beginningBalance' => $beginningBalance,
            ]);
        }
    }

    /**
     * Vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su informacion
     *
     * @return Response|JsonResponse|\Illuminate\View\View
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

        /* Cálcula el saldo inicial que tendra la cuenta*/
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
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/balanceCheckUp/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Balance de Comprobación');
        $pdf->setFooter();
        $sign = $pdf->setBody('accounting::pdf.checkup_balance', true, [
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
     * @param  integer         $currency_id identificador de la moneda a la cual se realizara la conversión
     *
     * @return array                        lista de conversiones actualizada
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
     * Verifica el salto de página
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }
}
