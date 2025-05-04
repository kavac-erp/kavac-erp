<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use DateTime;
use Carbon\Carbon;
use App\Models\Parameter;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReportRepository;
use Illuminate\Http\JsonResponse;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\ExchangeRate;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\Accounting\Exports\AccountingBalanceSheetExport;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;

/**
 * @class AccountingBalanceSheetController
 * @brief Controlador para la generación del reporte de balance general
 *
 * Clase que gestiona el reporte de balance general
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class AccountingBalanceSheetController extends Controller
{
    /**
     * Establece el trigger de salto de página
     *
     * @var mixed $PageBreakTrigger
     */
    protected $PageBreakTrigger;

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
    protected $currency = null;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:accounting.report.balancesheet', [
            'only' => ['pdf', 'pdfVue', 'pdfSign', 'pdfVueSign'],
        ]);
    }

    /**
     * Obtiene el listado de conversiones
     *
     * @return array|mixed
     */
    public function getConvertions()
    {
        return $this->convertions;
    }

    /**
     * Establece el listado de conversiones
     *
     * @param mixed $convertions
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
     * @return string|int
     */
    public function getCurrencyId()
    {
        return $this->currency->id;
    }

    /**
     * Obtiene la moneda
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Establece la moneda
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
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  string   $date     Fecha
     * @param  string   $level    Nivel de sub cuentas máximo a mostrar
     * @param  Currency $currency Moneda en que se expresará el reporte
     * @param  boolean  $zero     Indica si se tomaran cuentas con saldo cero
     *
     * @return JsonResponse
     */
    public function pdfVue($date, $level, Currency $currency, $zero = false)
    {
        /* Último día correspondiente al mes */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /* Formatea la fecha final de búsqueda, (YYYY-mm-dd HH:mm:ss) */
        $endDate = $date . '-' . $day;

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /*
         * consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN,
         * registros de las cuentas patrimoniales seleccionadas
         */
        $query = AccountingAccount::with('entryAccount.entries.currency')
            ->with(['entryAccount.entries' => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                }
            }])
            ->whereHas('entryAccount.entries', function ($query) use ($endDate, $institution_id, $is_admin) {
                $query->where('from_date', '<=', $endDate)->where('approved', true)
                    ->where('institution_id', $institution_id);
            })
            ->whereIn('group', [1, 2, 3, 4])
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

        $convertions = [];

        /* Se recorre y evalúa la relación en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency->id
                        );
                    }

                    foreach ($convertions as $convertion) {
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
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency->id
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

        /* Enlace para el reporte */
        $url = 'BalanceSheet/' . $endDate . '/' . $level . '/' . $zero;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Almacena el registro del reporte del día si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Balance General')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte*/
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Balance General',
                    'url' => $url,
                    'currency_id' => $currency->id,
                    'institution_id' => $institution_id,
                ]
            );
        } else {
            $report->url = $url;
            $report->currency_id = $currency->id;
            $report->institution_id = $institution_id;
            $report->save();
        }

        /*
         * El siguiente segmento es necesario para evaluar cuando se va a mostrar
         * información y cuando no en el reporte.
         * [$records Registros disponibles para mostrarse]
         */

        $endDate = explode('/', $report->url)[1];
        $level = explode('/', $report->url)[2];
        $zero = explode('/', $report->url)[3] == 'true' ? true : false;
        $this->setCurrency($report->currency);

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')
                ->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $records = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->whereIn('group', [1, 2, 3, 4]);
                });
        }])
            ->where('institution_id', $institution->id)
            ->where('from_date', '<=', $endDate)
            ->where('approved', true)
            ->get();

        $dataInToEndDate = $records->toArray();
        if ($dataInToEndDate == []) {
            return response()->json(
                [
                    'result' => false,
                    'message' => 'No se ha encontrado ningún registro que cumpla con los parámetros establecidos.',
                ],
                200
            );
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  string   $date     Fecha
     * @param  string   $level    Nivel de sub cuentas máximo a mostrar
     * @param  Currency $currency Moneda en que se expresará el reporte
     * @param  boolean  $zero     Si se tomaran cuentas con saldo cero
     *
     * @return JsonResponse
     */
    public function pdfVueSign($date, $level, Currency $currency, $zero = false)
    {
        /* Último día correspondiente al mes */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /* Formatea la fecha final de busqueda, (YYYY-mm-dd HH:mm:ss) */
        $endDate = $date . '-' . $day;

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /*
         * Consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN,
         * registros de las cuentas patrimoniales seleccionadas
         */
        $query = AccountingAccount::with('entryAccount.entries.currency')
            ->with(['entryAccount.entries' => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        $query->where('from_date', '<=', $endDate)->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                }
            }])
            ->whereHas('entryAccount.entries', function ($query) use ($endDate, $institution_id, $is_admin) {
                $query->where('from_date', '<=', $endDate)->where('approved', true)
                    ->where('institution_id', $institution_id);
            })
            ->whereIn('group', [1, 2, 3, 4])
            ->where('subgroup', 0)
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

        $convertions = [];

        /* Se recorre y evalúa la relación en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency->id
                        );
                    }

                    foreach ($convertions as $convertion) {
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
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)
                        && $entryAccount['entries']['currency']['id'] != $currency->id
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

        /* Enlace para el reporte */
        $url = 'BalanceSheetSign/' . $endDate . '/' . $level . '/' . $zero;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Almacena el registro del reporte del día si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Balance General')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Balance General',
                    'url' => $url,
                    'currency_id' => $currency->id,
                    'institution_id' => $institution_id,
                ]
            );
        } else {
            $report->url = $url;
            $report->currency_id = $currency->id;
            $report->institution_id = $institution_id;
            $report->save();
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * Genera el reporte en hojade calculo de balance general
     *
     * @param  integer $report id de reporte y su información
     *
     * @return void
     */
    public function export($report)
    {
        return  $this->pdf($report, true);
    }
    /**
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     * @param  boolean $xml si se requiere el reporte en xml
     *
     * @return mixed
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
        }
        $endDate = explode('/', $report->url)[1];
        $level = explode('/', $report->url)[2];
        $zero = explode('/', $report->url)[3] == 'true' ? true : false;
        $date = count(explode('-', $endDate)) > 1 ?
        explode('-', $endDate)[0] . '-' . explode('-', $endDate)[1] :
        explode('-', $endDate)[0];

        $this->setCurrency($report->currency);

        $institution = null;

        $is_admin = auth()->user()->isAdmin();

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }
        $institution_id = $institution->id;
        $arr = [];

        $formDate = $date . '-01';
        $fecha = Carbon::createFromFormat('Y-m-d', $formDate);
        if ($fecha->month == 1) {
            $lastAvalible = false;
        } else {
            $lastAvalible = true;
        }
        $mesAnterior = $fecha->subMonth();
        $initDate2 = $mesAnterior->copy()->startOfMonth();
        $endDate2 = $mesAnterior->copy()->endOfMonth();

        if (count(explode('-', $endDate)) > 1) {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($formDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true);
                            }
                        }
                    }
                },
            ]);
            $currency_id = $this->currency->id;
            $lastMonthBalances = AccountingAccount::select(
                'accounting_accounts.denomination',
                'accounting_accounts.id',
                'accounting_entries.id as entry_id',
                DB::raw(
                    "CONCAT(
                        accounting_accounts.group, '.',
                        accounting_accounts.subgroup, '.',
                        accounting_accounts.item, '.',
                        accounting_accounts.generic, '.',
                        accounting_accounts.specific, '.',
                        accounting_accounts.subspecific, '.',
                        accounting_accounts.institutional
                    ) AS code_account"
                ),
                'accounting_entries.reference',
                'accounting_entries.concept',
                'accounting_entries.from_date',
                DB::raw(
                    "CASE
                        WHEN accounting_accounts.group IN ('1', '4', '6') THEN
                            (
                                SELECT SUM(debit - assets)
                                FROM accounting_entry_accounts
                                WHERE accounting_entry_accounts.accounting_account_id = accounting_accounts.id
                                AND accounting_entry_accounts.accounting_entry_id = accounting_entries.id
                                AND accounting_entry_accounts.deleted_at IS NULL
                            )
                        WHEN accounting_accounts.group IN ('2', '3', '5') THEN
                            (
                                SELECT SUM(assets - debit)
                                FROM accounting_entry_accounts
                                WHERE accounting_entry_accounts.accounting_account_id = accounting_accounts.id
                                AND accounting_entry_accounts.accounting_entry_id = accounting_entries.id
                                AND accounting_entry_accounts.deleted_at IS NULL
                            )
                        ELSE 0
                    END AS total"
                )
            )
                ->leftJoin('accounting_entry_accounts', 'accounting_accounts.id', '=', 'accounting_entry_accounts.accounting_account_id')
                ->leftJoin('accounting_entries', 'accounting_entry_accounts.accounting_entry_id', '=', 'accounting_entries.id')
                ->whereIn('accounting_accounts.group', [1, 2, 3])
                ->where(function ($query) use ($formDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->where('accounting_entries.from_date', '<', $formDate)
                            ->where('accounting_entries.approved', true)
                            ->where('accounting_entries.institution_id', $institution_id)
                        ) {
                            $query->where('accounting_entries.from_date', '<', $formDate)
                                ->where('accounting_entries.approved', true)
                                ->where('accounting_entries.institution_id', $institution_id)
                                ->where('accounting_entries.deleted_at', null);
                        }
                    } else {
                        if ($is_admin) {
                            if (
                                $query->where('accounting_entries.from_date', '<', $formDate)
                                ->where('accounting_entries.approved', true)
                            ) {
                                $query->where('accounting_entries.from_date', '<', $formDate)
                                    ->where('accounting_entries.approved', true)
                                    ->where('accounting_entries.deleted_at', null);
                            }
                        }
                    }
                })
                ->whereRaw('accounting_entry_accounts.deleted_at IS NULL')
                ->groupBy('accounting_accounts.denomination', 'accounting_entries.id', 'accounting_accounts.id', 'accounting_accounts.group', 'accounting_accounts.subgroup', 'accounting_accounts.item', 'accounting_accounts.generic', 'accounting_accounts.specific', 'accounting_accounts.subspecific', 'accounting_accounts.institutional', 'accounting_entries.reference', 'accounting_entries.concept', 'accounting_entries.from_date')
                ->orderBy('accounting_entries.from_date', 'ASC')
                ->get();
        } else {
            $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($date, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereYear('from_date', $date)->where('approved', true)
                            ->where('institution_id', $institution_id)
                        ) {
                            $query->whereYear('from_date', $date)->where('approved', true)
                                ->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereYear('from_date', $date)->where('approved', true)) {
                                $query->whereYear('from_date', $date)->where('approved', true);
                            }
                        }
                    }
                },
            ]);
            $lastMonthBalances = [];
        }
        $query = $query->whereIn('group', [1, 2, 3])
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();
        foreach ($query as $account) {
            if (!$account->entryAccount->isEmpty()) {
                foreach ($account->entryAccount as $entrie) {
                    if (!is_null($entrie->entries)) {
                        $balance = $this->getRealBalaceCalculator(
                            $account->code[0],
                            $entrie->debit,
                            $entrie->assets
                        );
                        if (!array_key_exists($account->code, $arr)) {
                            $data = [
                                "id" => $account->id,
                                "code" => $account->code,
                                "denomination" => $account->denomination,
                                "balance" => $balance,
                                "lastMonthBalance" => 0,
                                "level" => 6,
                                "parent" => [],
                            ];
                            $arr[$account->code] = $data;
                        } else {
                            $arr[$account->code]['balance'] += $balance;
                        }
                    }
                }
            } else {
                $data = [
                    "id" => $account->id,
                    "code" => $account->code,
                    "denomination" => $account->denomination,
                    "balance" => 0,
                    "lastMonthBalance" => 0,
                    "level" => 6,
                    "parent" => [],
                ];
                $arr[$account->code] = $data;
            }
        }
        foreach ($lastMonthBalances as $account) {
            $balance = 0;
            if (!array_key_exists($account->code_account, $arr)) {
                $data = [
                    "id" => $account->id,
                    "code" => $account->code_account,
                    "denomination" => $account->denomination,
                    "balance" => 0,
                    "lastMonthBalance" => $account->total,
                    "level" => 6,
                    "parent" => [],
                ];
                $arr[$account->code_account] = $data;
            } else {
                $arr[$account->code_account]['lastMonthBalance'] += $account->total;
            }
        }
        $parentsArray = [];
        foreach ($arr as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($arr[$key]);
            }
        }
        $accountingAccount = AccountingAccount::where('group', "3")
            ->where('subgroup', "2")
            ->where('item', "5")
            ->where('generic', "02")
            ->where('specific', "01")
            ->where('subspecific', "01")
            ->where('institutional', "001")
            ->where('active', true)->first();

        $accountValue = Parameter::where('p_key', 'close_fiscal_year_account')->get('p_value')->first()?->p_value;
        if ($accountValue) {
            $nameEDR = AccountingAccount::find($accountValue)->code;
        } else {
            $nameEDR = $accountingAccount->code;
        }

        if ($accountingAccount) {
            $data = [
                "id" => $accountingAccount->id,
                "code" => $nameEDR,
                "denomination" => "RESULTADOS DEL EJERCICIO",
                "balance" => $this->getTotalResults($endDate, $formDate),
                "level" => 2,
                "lastMonthBalance" => $this->getLastTotalResults($initDate2, $endDate2, $lastAvalible),
                "parent" => $accountingAccount->parent_id,
            ];
            $arr["3.2.5.02.01.01.001"] = $data;
        } else {
            $data = [
                "id" => 348,
                "code" => $nameEDR,
                "denomination" => "RESULTADOS DEL EJERCICIO",
                "balance" => $this->getTotalResults($endDate, $formDate),
                "level" => 2,
                "lastMonthBalance" => $this->getLastTotalResults($initDate2, $endDate2, $lastAvalible),
                "parent" => 346,
            ];
            $arr["3.2.5.02.01.01.001"] = $data;
        }

        foreach ($arr as $finalAccount) {
            $parents = $this->getAccountParents($finalAccount, []);
            array_push($parentsArray, $parents);
        }

        foreach ($parentsArray as $pArray) {
            foreach ($pArray as $pA) {
                if (!array_key_exists($pA->code, $arr)) {
                    $data = [
                        "id" => $pA['id'],
                        "code" => $pA['code'],
                        "denomination" => $pA['denomination'],
                        "balance" => $pA['balance'],
                        "lastMonthBalance" => $pA['lastMonthBalance'],
                        "level" => $pA['level'] < 0 ? $pA['level'] * -1 : $pA['level'],
                        "parent" => $pA['parent_id'],
                    ];
                    $arr[$pA->code] = $data;
                } else {
                    $childrenCode = explode('.', $pA['code']);
                    if ($childrenCode[6] == '000' && $arr[$pA->code]['code'] != '3.2.5.02.01.01.001') {
                        $arr[$pA->code]['balance'] += $pA['balance'];
                        $arr[$pA->code]['lastMonthBalance'] += $pA['lastMonthBalance'];
                    }
                }
            }
        }
        ksort($arr);

        $replacements = array(
            "0" => array("value" => "0", "length" => 1),
            "00.000" => array("value" => "00.000", "length" => 6),
            "00.00.000" => array("value" => "00.00.000", "length" => 9),
            "00.00.00.000" => array("value" => "00.00.00.000", "length" => 12),
            "0.00.00.00.000" => array("value" => "0.00.00.00.000", "length" => 14),
            "0.0.00.00.00.000" => array("value" => "0.0.00.00.00.000", "length" => 16),
        );

        foreach ($arr as $key => &$value) {
            foreach ($replacements as $replacement) {
                $length = $replacement['length'];
                if (substr($key, -$length) != $replacement['value']) {
                    $sum = $value['balance'];
                    $key_zero = substr_replace($key, $replacement['value'], -$length);
                }
            }
        }

        if ($level == 1) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[1] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 2) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[2] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 3) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[3] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 4) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[4] != 0) {
                    unset($arr[$key]);
                }
            }
        } elseif ($level == 5) {
            foreach ($arr as $key => $a) {
                $b = explode('.', $key);

                if ($b[5] != 0) {
                    unset($arr[$key]);
                }
            }
        }

        $totArr = [];

        foreach ($arr as $key => $a) {
            if ($a['code'][0] == 1) {
                $totArr[1][$key] = $a;
            } elseif ($a['code'][0] == 2) {
                $totArr[2][$key] = $a;
            } elseif ($a['code'][0] == 3) {
                $totArr[3][$key] = $a;
            } elseif ($a['code'][0] == 4) {
                $totArr[4][$key] = $a;
            }
        }

        /* Configuración general de la apliación */
        $setting = Setting::all()->first();

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definición de las características generales de la página pdf */

        if (count(explode('-', $endDate)) > 1) {
            $lastOfThePreviousMonth = date(
                'd',
                (mktime(0, 0, 0, explode('-', $date)[1], 1, explode('-', $date)[0]) - 1)
            );
            $last = $lastOfThePreviousMonth . '/' . (explode('-', $date)[1] - 1) . '/' . explode('-', $date)[0];
        } else {
            $last = '';
        }
        $institution = Institution::find(1);
        if ($xml) {
            return Excel::download(new AccountingBalanceSheetExport([
            'pdf' => $pdf,
            'records' => $totArr,
            'currency' => $this->getCurrency(),
            'level' => $level,
            'zero' => $zero,
            'endDate' => $endDate,
            'monthBefore' => $last,
            'institution' => $institution,
            ]), now()->format('d-m-Y') . '_ESTADO_DE_SITUACIÓN_FINANCIERA.xlsx');
        } else {
            $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/balanceSheet/' . $report->id)]);
            $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Estado de Situación financiera');
            $pdf->setFooter();
            $pdf->setBody('accounting::pdf.balance_sheet', true, [
            'pdf' => $pdf,
            'records' => $totArr,
            'currency' => $this->getCurrency(),
            'level' => $level,
            'zero' => $zero,
            'endDate' => $endDate,
            'monthBefore' => $last,
            'institution' => $institution,
            ]);
        }
    }

    /**
     * Genera el reporte en pdf de balance general
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     *
     * @return mixed
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
        $endDate = explode('/', $report->url)[1];
        $level = explode('/', $report->url)[2];
        $zero = explode('/', $report->url)[3];
        $date = explode('-', $endDate)[0] . '-' . explode('-', $endDate)[1];
        $this->setCurrency($report->currency);

        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* Establece la consulta de ralación de nivel 1 que se desea realizar */
        $level_1 = 'entryAccount.entries';

        /* Establece la consulta de ralación de nivel 2 que se desea realizar */
        $level_2 = 'children.entryAccount.entries';

        /* Establece la consulta de ralación de nivel 3 que se desea realizar */
        $level_3 = 'children.children.entryAccount.entries';

        /* Establece la consulta de ralación de nivel 4 que se desea realizar */
        $level_4 = 'children.children.children.entryAccount.entries';

        /* Establece la consulta de ralación de nivel 4 que se desea realizar */
        $level_5 = 'children.children.children.children.entryAccount.entries';

        /* Establece la consulta de ralación de nivel 6 que se desea realizar */
        $level_6 = 'children.children.children.children.children.entryAccount.entries';

        /* Se realiza la consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN */
        $records = AccountingAccount::with($level_1, $level_2, $level_3, $level_4, $level_5, $level_6)
            ->with([$level_1 => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id);
                } elseif ($is_admin) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true);
                }
            }])
            ->with([$level_2 => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id);
                } elseif ($is_admin) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true);
                }
            }])
            ->with([$level_3 => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id);
                } elseif ($is_admin) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true);
                }
            }])
            ->with([$level_4 => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id);
                } elseif ($is_admin) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true);
                }
            }])
            ->with([$level_5 => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id);
                } elseif ($is_admin) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true);
                }
            }])
            ->with([$level_6 => function ($query) use ($endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true)
                        ->where('institution_id', $institution_id);
                } elseif ($is_admin) {
                    $query->where('from_date', '<=', $endDate)->where('approved', true);
                }
            }])
            ->whereIn('group', [1, 2, 3, 4])
            ->where('subgroup', 0)
            ->orderBy('subgroup', 'ASC')
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')->get();

        /* Registros de las cuentas */
        $records = $this->formatDataInArray($records, $date, $endDate);

        /* Configuración general de la apliación */
        $setting = Setting::all()->first();

        /* Base para generar el pdf */
        $pdf = new ReportRepositorySign();

        /* Definición de las características generales de la página pdf */

        $lastOfThePreviousMonth = date('d', (mktime(0, 0, 0, explode('-', $date)[1], 1, explode('-', $date)[0]) - 1));
        $last = ($lastOfThePreviousMonth . '/' . (explode('-', $date)[1] - 1) . '/' . explode('-', $date)[0]);

        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/balanceSheetSign/' . $report->id)]);

        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Estado de Situación financiera');
        $pdf->setFooter();
        $sign = $pdf->setBody('accounting::pdf.balance_sheet', true, [
            'pdf' => $pdf,
            'records' => $records,
            'currency' => $this->getCurrency(),
            'level' => $level,
            'zero' => $zero,
            'endDate' => $endDate,
            'monthBefore' => $last,
            'institution' => $institution,
        ]);
        if ($sign['status'] == 'true') {
            return response()->download($sign['file'], $sign['filename'], [], 'inline');
        } else {
            return response()->json(['result' => $sign['status'], 'message' => $sign['message']], 200);
        }
    }

    /**
     * Sintetiza la información de una cuenta en un array con sus respectivas subcuentas
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  \Modules\Accounting\Models\AccountingAccount|array $records Registro de una cuenta o subcuenta patrimonial
     * @param  int $level Contador que indica el nivel de profundidad de la recursividad para obtener subcuentas de una cuenta
     *
     * @return array
     */
    public function formatDataInArray($records, $initD, $endD, $level = 1)
    {
        /* Información pertinente de la consulta */
        $parent = [];

        /* Posición de la cuenta base */
        $pos = 0;

        /* Condición de parada del último nivel */
        if ($level > 6) {
            return [];
        }

        $lastOfThePreviousMonth = date('d', (mktime(0, 0, 0, explode('-', $initD)[1], 1, explode('-', $initD)[0]) - 1));

        if (count($records) > 0) {
            foreach ($records as $account) {
                array_push($parent, [
                    'code' => $account->getCodeAttribute(),
                    'denomination' => $account->denomination,
                    'balance' => $this->calculateValuesInEntries(
                        $account,
                        /*explode('-', $endD)[0] . '-' . explode('-', $endD)[1] . '-01',
                        $endD*/
                    ),
                    // acumulado de los meses anteriores
                    'lastMonthBalance' => $this->calculateValuesInEntries(
                        $account,
                        /*explode('-', $initD)[0] . '-01-01',
                        explode('-', $endD)[0] . '-' . (explode('-', $endD)[1] - 1) . '-' . $lastOfThePreviousMonth*/
                    ),
                    'level' => $level,
                    'children' => [],
                ]);
                $parent[$pos]['children'] = $this->formatDataInArray($account->children, $initD, $endD, $level + 1);
                $pos++;
            }
            return $parent;
        }
        return [];
    }

    /**
     * Realiza el cálculo de saldo de la cuenta tomando en cuenta todos sus subcuentas,
     * hasta llegar al último nivel de parentela solo se sumaran los valores de los asientos
     * contables aprobados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param AccountingAccount $account Registro de una cuenta o subcuenta patrimonial
     *
     * @return float Resultado de realizar la operaciones de suma y resta
     */
    public function calculateValuesInEntries($account)
    {
        /* Saldo total en el debe de la cuenta */
        $debit = 0.00;

        /* Saldo total en el haber de la cuenta */
        $assets = 0.00;

        /* Saldo total de la suma de los saldos de sus cuentas hijo */
        $balanceChildren = 0.00;

        foreach ($account->entryAccount as $entryAccount) {
            if ($entryAccount->entries && $entryAccount->entries['approved']) {
                if (!array_key_exists($entryAccount['entries']['currency']['id'], $this->getConvertions())) {
                    $this->setConvertions($this->calculateExchangeRates(
                        $this->getConvertions(),
                        $entryAccount['entries'],
                        $this->getCurrencyId()
                    ));
                }

                $debit += ($entryAccount['debit'] != 0) ?
                $this->calculateOperation(
                    $this->getConvertions(),
                    $entryAccount['entries']['currency']['id'],
                    $entryAccount['debit'],
                    $entryAccount['entries']['from_date'],
                    ($entryAccount['entries']['currency']['id'] == $this->getCurrencyId()) ?? false
                ) : 0;

                $assets += ($entryAccount['assets'] != 0) ?
                $this->calculateOperation(
                    $this->getConvertions(),
                    $entryAccount['entries']['currency']['id'],
                    $entryAccount['assets'],
                    $entryAccount['entries']['from_date'],
                    ($entryAccount['entries']['currency']['id'] == $this->getCurrencyId()) ?? false
                ) : 0;
            }
        }

        if (count($account->children) > 0) {
            foreach ($account->children as $child) {
                /* llamada recursiva y acumulación */
                $balanceChildren += $this->calculateValuesInEntries($child);
            }
        }
        return (($debit - $assets) + $balanceChildren);
    }

    /**
     * Realiza la conversion de saldo
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  array   $convertions   Lista de tipos cambios para la moneda
     * @param  integer $currency_id   Identificador de la moneda
     * @param  float   $value         Saldo del asiento
     * @param  float   $date          Fecha del asiento
     * @param  boolean $equalCurrency Bandera que indica si el tipo de moneda en el que esta el asiento es las misma
     *                                que la que se desea expresar
     *
     * @return float                  Resultdado de la operación
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
     * @param  array           $convertions Lista de conversiones
     * @param  AccountingEntry $entry       Asiento contable
     * @param  integer         $currency_id Identificador de la moneda a la cual se realizara la conversión
     *
     * @return array                        Lista de conversiones actualizada
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
                            'amount' => $recordExchangeRate->amount,
                            'operator' => ($currency_id == $recordExchangeRate->from_currency_id) ? 'from' : 'to',
                            'start_at' => $recordExchangeRate->start_at,
                            'end_at' => $recordExchangeRate->end_at,
                        ]
                    );
                }
            }
        }
        return $convertions;
    }

    /**
     * Obtiene el valor de la bandera de salto de página
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }

    /**
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author Ing. Francisco Escala <fjescala@gmail.com>
     *
     * @param  integer $ini     Identificador del tipo de cuenta
     * @param  integer $debit   Valor de la cuenta por la columna del debe
     * @param  integer $assets  Valor de la cuenta por la columna del haber
     *
     * @return float           Retorna el valor del total de las cuentas en el asiento
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
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author Ing. Francisco Escala <fjescala@gmail.com>
     *
     * @param  string $date     Fecha de la consulta
     * @param  string $formDate Fecha de la consulta
     *
     * @return float           Retorna el valor del total de las cuentas en el asiento
     */

    public function getTotalResults($date, $formDate)
    {
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

        $records = AccountingEntry::with(['accountingAccounts' => function ($query) {
            $query->with('account')
                ->whereHas('account', function ($query) {
                    $query->whereIn('group', [5, 6]);
                });
        }])
            ->where('institution_id', $institution->id)
            ->where('approved', true)
            ->whereBetween('from_date', [$formDate, $date])
            ->get();

        foreach ($records as $record) {
            if ($record['accountingAccounts']) {
                foreach ($record['accountingAccounts'] as $account) {
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
                            "balance" => $balance,
                            "lastMonthBalance" => 0,
                            "level" => 6,
                            "parent" => [],
                        ];
                        $arr[$account['account']['code']] = $data;
                    } else {
                        $arr[$account['account']['code']]['balance'] += $balance;
                    }
                }
            }
        }
        foreach ($arr as $key => $a) {
            if ($a['code'][0] == 6) {
                $result_of_the_excersice -= $a['balance'];
            } elseif ($a['code'][0] == 5) {
                $result_of_the_excersice += $a['balance'];
            }
        }

        return $result_of_the_excersice;
    }

    /**
     * Realiza el cálculo de las cuentas de acuerdo a como suman
     *
     * @author Ing. Francisco Escala <fjescala@gmail.com>
     *
     * @param  object  $formDate     Fecha de la consulta
     * @param  object  $endDate      Fecha de la consulta
     * @param  boolean $lastAvalible Última disponibilidad
     *
     * @return float           Retorna el valor del total de las cuentas en el asiento
     */

    public function getLastTotalResults($formDate, $endDate, $lastAvalible)
    {
        if (!$lastAvalible) {
            return 0;
        }
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
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        $beginnBalances = AccountingEntry::query()
            ->with(['accountingAccounts' => function ($query) {
                $query
                    ->with('account')
                    ->whereHas('account', function ($query) {
                        $query->whereIn('group', [5, 6]);
                    });
            }])
            ->where('institution_id', $institution->id)
            ->where('approved', true)
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
                        $arr[$account['account']['code']]['lastMonthBalance'] += $balance;
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
            if ($a['code'][0] == 6) {
                $result_of_the_excersice -= $a['lastMonthBalance'];
            } elseif ($a['code'][0] == 5) {
                $result_of_the_excersice += $a['lastMonthBalance'];
            }
        }

        return $result_of_the_excersice;
    }

    /**
     * Método para buscar las cuentas padre de una formulación
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array con las cuentas padre de la formulación
     */
    public function getAccountParents($childData, $parents = [])
    {
        $child = AccountingAccount::findOrFail($childData['id']);
        $child['balance'] = $childData['balance'];
        $child['lastMonthBalance'] = $childData['lastMonthBalance'];
        $child['level'] = $childData['level'] - 1;
        if (!isset($child)) {
            return $parents;
        }

        if (!array_key_exists($child->id, $parents)) {
            $parents[$child->id] = $child;
        }

        if ($child->parent_id == null) {
            return $parents;
        } else {
            $child->load('parent');
            $parent = $child->parent;
            $parent['balance'] += $child['balance'];
            $parent['lastMonthBalance'] += $child['lastMonthBalance'];
            $parent['level'] = $child['level'];

            return $this->getAccountParents($parent, $parents);
        }
    }
}
