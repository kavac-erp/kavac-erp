<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use DateTime;
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
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\Accounting\Exports\AccountingStateOfResultsExport;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;

/**
 * @class AccountingStateOfResultsController
 * @brief Controlador para la generación del reporte de estado de resultados
 *
 * Clase que gestiona el reporte de estado de resultados
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class AccountingStateOfResultsController extends Controller
{
    /**
     * Salto de página
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
        $this->middleware('permission:accounting.report.stateofresults', [
            'only' => [
                'pdf',
                'pdfVue',
                'pdfSign',
                'pdfVueSign'
            ]
        ]);
    }

    /**
     * Obtiene las conversiones de cuentas
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
     * @param array $convertions Lista de conversiones de cuentas
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
     * @return integer|string
     */
    public function getCurrencyId()
    {
        return $this->currency->id;
    }

    /**
     * Obtiene los datos de la moneda
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
     * @param  Currency $currency Datos de la moneda
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
     * @param  string   $date     fecha
     * @param  string   $level    nivel de sub cuentas maximo a mostrar
     * @param  Currency $currency moneda en que se expresara el reporte
     * @param  boolean  $zero     si se tomaran cuentas con saldo cero
     *
     * @return JsonResponse
     */
    public function pdfVue($date, $level, Currency $currency, $zero = false)
    {
        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        if (explode('-', $date)[1] != '') {
            /* último dia correspondiente al mes */
            $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

            /* formatea la fecha final de busqueda, (YYYY-mm-dd HH:mm:ss) */
            $endDate = $date . '-' . $day;

            /* consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN */
            /* registros de las cuentas patrimoniales seleccionadas */
            $query = AccountingAccount::with('entryAccount.entries.currency')
                ->with(['entryAccount.entries' => function ($query) use ($endDate, $date, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                            ->where('approved', true)->where('institution_id', $institution_id)
                        ) {
                            $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                                ->where('approved', true)->where('institution_id', $institution_id);
                        }
                    } else {
                        if ($is_admin) {
                            $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                                ->where('approved', true)->where('institution_id', $institution_id);
                        }
                    }
                }])
                ->whereHas('entryAccount.entries', function ($query) use ($endDate, $date, $institution_id, $is_admin) {
                    $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                        ->where('approved', true)->where('institution_id', $institution_id);
                })
                ->whereBetween('group', [5, 6])
                ->orderBy('group', 'ASC')
                ->orderBy('subgroup', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')->get();
        } else {
            $endDate = explode('-', $date)[0];
            /* consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN */
            /* registros de las cuentas patrimoniales seleccionadas */
            $query = AccountingAccount::with('entryAccount.entries.currency')
                ->with(['entryAccount.entries' => function ($query) use ($endDate, $institution_id) {
                    $query
                        ->where('approved', true)
                        ->where('institution_id', $institution_id)
                        ->whereYear('from_date', $endDate);
                }])
                ->whereHas('entryAccount.entries', function ($query) use ($endDate, $institution_id) {
                    $query
                        ->where('approved', true)
                        ->where('institution_id', $institution_id)
                        ->whereYear('from_date', $endDate);
                })
                ->whereBetween('group', [5, 6])
                ->orderBy('group', 'ASC')
                ->orderBy('subgroup', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')
                ->get();
        }

        $convertions = [];

        /* Se recorre y evalua la relacion en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
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
                        && $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                            . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                            ', verificar tipos de cambio configurados. ',
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
        $zero = ($zero) ? 'true' : '';
        $url = 'StateOfResults/' . $endDate . '/' . $level . '/' . $zero;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* almacena el registro del reporte del dia si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Estado de Resultados')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Estado de Resultados',
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
        $date = count(explode('-', $endDate)) > 1 ?
            explode('-', $endDate)[0] . '-' . explode('-', $endDate)[1] :
            explode('-', $endDate)[0];

        $this->setCurrency($report->currency);

        if ($is_admin) {
            $institution = Institution::where('default', true)->first();
        } else {
            $user_profile = Profile::with('institution')
            ->where('user_id', auth()->user()->id)->first();
            $institution = $user_profile['institution'] ?? null;
        }

        if (count(explode('-', $endDate)) > 1) {
            $records = AccountingEntry::query()
                ->with(['accountingAccounts' => function ($query) {
                    $query
                        ->with('account')
                        ->whereHas('account', function ($query) {
                            $query->whereIn('group', [5, 6]);
                        });
                }])
                ->where('currency_id', $this->currency->id)
                ->where('concept', '<>', 'Cierre de ejercicio')
                ->where('institution_id', $institution->id)
                ->whereBetween('from_date', [$date . '-01', $endDate])
                ->get();
        } else {
            $records = AccountingEntry::query()
                ->with(['accountingAccounts' => function ($query) {
                    $query
                        ->with('account')
                        ->whereHas('account', function ($query) {
                            $query
                                ->where('resource', true)
                                ->orWhere('egress', true);
                        });
                }])
                ->where('currency_id', $this->currency->id)
                ->where('concept', '<>', 'Cierre de ejercicio')
                ->where('approved', true)
                ->where('institution_id', $institution->id)
                ->whereYear('from_date', $date)
                ->get();
        }

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
     * @param  string   $date     fecha
     * @param  string   $level    nivel de sub cuentas maximo a mostrar
     * @param  Currency $currency moneda en que se expresara el reporte
     * @param  boolean  $zero     si se tomaran cuentas con saldo cero
     *
     * @return JsonResponse
     */
    public function pdfVueSign($date, $level, Currency $currency, $zero = false)
    {
        /* último dia correspondiente al mes */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /* formatea la fecha final de busqueda, (YYYY-mm-dd HH:mm:ss) */
        $endDate = $date . '-' . $day;

        $institution_id = null;

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* consulta de cada cuenta y asiento que pertenezca a ACTIVO, PASIVO, PATRIMONIO y CUENTA DE ORDEN */
        /* registros de las cuentas patrimoniales seleccionadas */
        $query = AccountingAccount::with('entryAccount.entries.currency')
            ->with(['entryAccount.entries' => function ($query) use ($endDate, $date, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                        ->where('approved', true)->where('institution_id', $institution_id)
                    ) {
                        $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                            ->where('approved', true)->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                            ->where('approved', true)->where('institution_id', $institution_id);
                    }
                }
            }])
            ->whereHas('entryAccount.entries', function ($query) use ($endDate, $date, $institution_id, $is_admin) {
                $query->whereBetween('from_date', [explode('-', $date)[0] . '-01-01', $endDate])
                    ->where('approved', true)->where('institution_id', $institution_id);
            })
            ->whereBetween('group', [5, 6])
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

        $convertions = [];

        /* Se recorre y evalua la relacion en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
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
                        && $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
                        return response()->json([
                            'result' => false,
                            'message' => 'Imposible expresar ' . $entryAccount['entries']['currency']['symbol']
                            . ' en ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                            ', verificar tipos de cambio configurados. ',
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

        /* almacena el ultimo dia correspondiente al mes */
        $day = date('d', (mktime(0, 0, 0, explode('-', $date)[1] + 1, 1, explode('-', $date)[0]) - 1));

        /* formatea la fecha final de busqueda, (YYYY-mm-dd HH:mm:ss) */
        $endDate = $date . '-' . $day;

        /* Se guarda un registro cada vez que se genera un reporte, en caso de que ya exista se actualiza */
        $zero = ($zero) ? 'true' : '';
        $url = 'StateOfResultsSign/' . $endDate . '/' . $level . '/' . $zero;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* almacena el registro del reporte del dia si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Estado de Resultados')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Estado de Resultados',
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
     * Genera el reporte en hojade calculo de estado de resultados
     *
     * @param  integer $report id de reporte y su informacion
     *
     * @return mixed
     */
    public function export($report)
    {
        return  $this->pdf($report, true);
    }

    /**
     * Genera el reporte en pdf de estado de resultados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
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
        if (count(explode('-', $endDate)) > 1) {
                $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($formDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)->where('concept', '<>', 'Cierre de ejercicio')
                        ) {
                            $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)
                                ->where('institution_id', $institution_id)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)) {
                                $query->whereBetween('from_date', [$formDate, $endDate])->where('approved', true)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                            }
                        }
                    }
                }
                ]);

            $beginnBalances = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($formDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->where('from_date', '<', $formDate)->where('approved', true)
                            ->where('institution_id', $institution_id)->where('concept', '<>', 'Cierre de ejercicio')
                        ) {
                            $query->where('from_date', '<', $formDate)->where('approved', true)
                                ->where('institution_id', $institution_id)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->where('from_date', '<', $formDate)->where('approved', true)) {
                                $query->where('from_date', '<', $formDate)->where('approved', true)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                            }
                        }
                    }
                }
            ])->whereIn('group', [5,6])
                ->orderBy('group', 'ASC')
                ->orderBy('subgroup', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')->get();
        } else {
                $query = AccountingAccount::with([
                'entryAccount.entries' => function ($query) use ($date, $institution_id, $is_admin) {
                    if ($institution_id) {
                        if (
                            $query->whereYear('from_date', $date)->where('approved', true)
                            ->where('institution_id', $institution_id)->where('concept', '<>', 'Cierre de ejercicio')
                        ) {
                            $query->whereYear('from_date', $date)->where('approved', true)
                                ->where('institution_id', $institution_id)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                        }
                    } else {
                        if ($is_admin) {
                            if ($query->whereYear('from_date', $date)->where('approved', true)) {
                                $query->whereYear('from_date', $date)->where('approved', true)->where('currency_id', $this->currency->id)->where('concept', '<>', 'Cierre de ejercicio');
                            }
                        }
                    }
                }
                ]);

            $beginnBalances = [];
        }
        $query = $query->whereIn('group', [5,6])
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
                                "beginningBalance" => 0,
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
                    "beginningBalance" => 0,
                    "level" => 6,
                    "parent" => [],
                ];
                $arr[$account->code] = $data;
            }
        }

        foreach ($beginnBalances as $account) {
            $balance = 0;
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
                                "balance" => 0,
                                "beginningBalance" => $balance,
                                "level" => 6,
                                "parent" => [],
                            ];
                            $arr[$account->code] = $data;
                        } else {
                            $arr[$account->code]['beginningBalance'] += $balance;
                        }
                    }
                }
            }
        }

        $parentsArray = [];
        foreach ($arr as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($arr[$key]);
            }
        }
        foreach ($arr as $finalAccount) {
            $parents = $this->getAccountParents($finalAccount, []);
            array_push($parentsArray, $parents);
        }
        $arr = [];
        // este es un array de cuentas con sus padres mas proximos
        // este foreach agrega la cuenta 6.0.00.0 a $arr
        foreach ($parentsArray as $pArray) {
            foreach ($pArray as $pA) {
                if (!array_key_exists($pA->code, $arr)) {
                    $data = [
                        "id" => $pA['id'],
                        "code" => $pA['code'],
                        "denomination" => $pA['denomination'],
                        "balance" => $pA['balance'],
                        "beginningBalance" => $pA['beginningBalance'],
                        "level" => $pA['level'],
                        "parent" => [],
                    ];
                    $arr[$pA->code] = $data;
                } else {
                    $childrenCode = explode('.', $pA['code']);
                    if ($childrenCode[6] == '000' && $arr[$pA->code]['code'] != '3.1.5.02.00.00.000') {
                        $arr[$pA->code]['balance'] += $pA['balance'];
                        $arr[$pA->code]['beginningBalance'] += $pA['beginningBalance'];
                    }
                }
            }
        }
        if ($zero) {
            $zeroAccount = AccountingAccount::whereBetween('group', [5, 6])
                ->orderBy('group', 'ASC')
                ->orderBy('subgroup', 'ASC')
                ->orderBy('item', 'ASC')
                ->orderBy('generic', 'ASC')
                ->orderBy('specific', 'ASC')
                ->orderBy('subspecific', 'ASC')
                ->orderBy('denomination', 'ASC')->get();
            foreach ($zeroAccount as $pA) {
                if (!array_key_exists($pA->code, $arr)) {
                    $data = [
                        "id" => $pA['id'],
                        "code" => $pA['code'],
                        "denomination" => $pA['denomination'],
                        "balance" => $pA['balance'],
                        "beginningBalance" => $pA['beginningBalance'],
                        "level" => $pA['level'],
                        "parent" => [],
                    ];
                    $arr[$pA->code] = $data;
                }
            }
        }

        ksort($arr);

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

        /* configuración general de la apliación */
        $setting = Setting::all()->first();

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
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
            return Excel::download(new AccountingStateOfResultsExport([
            'pdf' => $pdf,
            'records' => $arr,
            'currency' => $this->getCurrency(),
            'level' => $level,
            'zero' => $zero,
            'endDate' => $endDate,
            'monthBefore' => $last,
            'institution' => $institution,
            ]), now()->format('d-m-Y') . '_ESTADO_DE_RENDIMIENTO_FINANCIERA.xlsx');
        } else {
            $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/stateOfResults/' . $report->id)]);
            $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Estado de Rendimiento financiero');
            $pdf->setFooter();
            $pdf->setBody('accounting::pdf.state_of_results', true, [
            'pdf' => $pdf,
            'records' => $arr,
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
     * Genera el reporte en pdf de estado de resultados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su informacion
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

        /* establece la consulta de ralación que se desean realizar */
        $level_1 = 'entryAccount.entries';

        /* establece la consulta de ralación que se desean realizar */
        $level_2 = 'children.entryAccount.entries';

        /* establece la consulta de ralación que se desean realizar */
        $level_3 = 'children.children.entryAccount.entries';

        /* establece la consulta de ralación que se desean realizar */
        $level_4 = 'children.children.children.entryAccount.entries';

        /* establece la consulta de ralación que se desean realizar */
        $level_5 = 'children.children.children.children.entryAccount.entries';

        /* establece la consulta de ralación que se desean realizar */
        $level_6 = 'children.children.children.children.children.entryAccount.entries';

        /* Se realiza la consulta de cada cuenta y asiento que pertenezca a INGRESOS Y GASTOS */
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
            ->whereBetween('group', [5, 6])
            ->where('subgroup', 0)
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')->get();

        /* registros de las cuentas */
        $records = $this->formatDataInArray($records, $date, $endDate);

        /* general de la apliación */
        $setting = Setting::all()->first();

        /* base para generar el pdf */
        $pdf = new ReportRepositorySign();

        /* Definicion de las caracteristicas generales de la página pdf */
        $lastOfThePreviousMonth = date('d', (mktime(0, 0, 0, explode('-', $date)[1], 1, explode('-', $date)[0]) - 1));
        $last = ($lastOfThePreviousMonth . '/' . (explode('-', $date)[1] - 1) . '/' . explode('-', $date)[0]);

        $institution = Institution::find(1);

        $pdf->setConfig(
            ['institution' => $institution,
            'urlVerify' => url('report/StateOfResultsSign/' . $report->id)]
        );
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de Estado de Rendimiento financiero');
        $pdf->setFooter();
        $sign = $pdf->setBody('accounting::pdf.state_of_results', true, [
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
     * @param  AccountingAccount|array $records registro de una cuenta o subcuenta patrimonial
     * @param  Date|string $initD Fecha inicial
     * @param  Date|string $endD  Fecha final
     * @param  integer $level contador que indica el nivel de profundidad de la recursividad para obtener subcuentas de una cuenta
     *
     * @return array
     */
    public function formatDataInArray($records, $initD, $endD, $level = 1)
    {
        /* información pertinente de la consultar */
        $parent = [];

        /* posición de la cuenta base */
        $pos = 0;

        /* condición de parada del ultimo nivel */
        if ($level > 6) {
            return [];
        }

        $lastOfThePreviousMonth = date('d', (mktime(0, 0, 0, explode('-', $initD)[1], 1, explode('-', $initD)[0]) - 1));

        if (count($records) > 0) {
            foreach ($records as $account) {
                array_push($parent, [
                    // mes seleccionado
                    'code' => $account->getCodeAttribute(),
                    'denomination' => $account->denomination,
                    'balance' => $this->calculateValuesInEntries(
                        $account,
                        explode('-', $endD)[0] . '-' . explode('-', $endD)[1] . '-01',
                        $endD
                    ),
                    // acumulado de los meses anteriores
                    'beginningBalance' => $this->calculateValuesInEntries(
                        $account,
                        explode('-', $initD)[0] . '-01-01',
                        explode('-', $endD)[0] . '-' . (explode('-', $endD)[1] - 1) . '-' . $lastOfThePreviousMonth
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
     * Cálculo de saldo de la cuenta tomando en cuenta todos sus subcuentas, hasta llegar al ultimo nivel
     * de parentela solo se sumaran los valores de los asientos contables aprobados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param object $records registro de una cuenta o subcuenta patrimonial
     *
     * @return float resultado de realizar la operaciones de suma y resta
     */
    public function calculateValuesInEntries($account, $initD, $endD)
    {
        $initDay = (int) explode('-', $initD)[2];
        $initMonth = (int) explode('-', $initD)[1];
        $initYear = (int) explode('-', $initD)[0];

        $endDay = (int) explode('-', $endD)[2];
        $endMonth = (int) explode('-', $endD)[1];
        $endYear = (int) explode('-', $endD)[0];

        if ($initDay < 10) {
            $initDay = '0' . $initDay;
        }
        if ($initMonth < 10) {
            $initMonth = '0' . $initMonth;
        }
        if ($endDay < 10) {
            $endDay = '0' . $endDay;
        }
        if ($endMonth < 10) {
            $endMonth = '0' . $endMonth;
        }

        $initD = $initYear . '-' . $initMonth . '-' . $initDay;
        $endD = $endYear . '-' . $endMonth . '-' . $endDay;

        /* saldo total en el debe de la cuenta */
        $debit = 0;

        /* saldo total en el haber de la cuenta */
        $assets = 0;

        /* saldo total de la suma de los saldos de sus cuentas hijo */
        $balanceChildren = 0;

        foreach ($account->entryAccount as $entryAccount) {
            if ($entryAccount->entries) {
                if (
                    $entryAccount->entries['from_date'] >= $initD && $entryAccount->entries['from_date'] <= $endD &&
                    $entryAccount->entries['approved']
                ) {
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
        }

        if (count($account->children) > 0) {
            foreach ($account->children as $child) {
                /* llamada recursiva y acumulación */
                $balanceChildren += $this->calculateValuesInEntries($child, $initD, $endD);
            }
        }
        return (($debit - $assets) + $balanceChildren);
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
     * @return float                  resultdado de la operacion
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
     * Devolver PageBreakTrigger
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }

    /**
     * Realiza el calculo de las cuentas de acuerdo a como suman
     *
     * @author fjescala <fjescala@gmail.com>
     *
     * @param  array   $convertions lista de tipos cambios para la moneda
     * @param  integer $ini         identificador del tipo de cuenta
     * @param  integer $debit       valor de la cuenta debe
     * @param  integer $assets      Valor de la cuenta en el haber
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
     * Método para buscar las cuentas padre de una formulación
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array con las cuentas padre de la formulación
     */
    public function getAccountParents($childData, $parents = [])
    {
        $child = AccountingAccount::find($childData['id']);
        $child['balance'] = $childData['balance'];
        $child['beginningBalance'] = $childData['beginningBalance'];
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
            $parent['beginningBalance'] += $child['beginningBalance'];
            $parent['level'] = $child['level'];
            return $this->getAccountParents($parent, $parents);
        }
    }
}
