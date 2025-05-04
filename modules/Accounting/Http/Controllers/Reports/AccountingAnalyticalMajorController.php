<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use DateTime;
use Carbon\Carbon;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReportRepository;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\ExchangeRate;
use Illuminate\Contracts\Support\Renderable;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingReportHistory;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;
use Modules\Accounting\Exports\AccountingAnalyticalMajorSheetExport;

/**
 * @class AccountingAnalyticalMajorController
 * @brief Controlador para la generación del reporte de Mayor Analítico
 *
 * Clase que gestiona el reporte de mayor analítico
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class AccountingAnalyticalMajorController extends Controller
{
    use ValidatesRequests;

    /**
     * Variable para controlar el salto de página
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
        /*
         * Establece permisos de acceso para cada método del controlador
         */
        $this->middleware(
            'permission:accounting.report.analiticalmajor',
            ['only' => ['index', 'getAccAccount', 'pdf', 'pdfVue', 'pdfSign', 'pdfVueSign']]
        );
    }

    /**
     * Consulta y formatea las cuentas en un rango determinado
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  int $initDate fecha inicial para iniciar la busqueda, formato(YYYY-mm)
     * @param  int $endYear  año del fin del rango de busqueda
     * @param  int $endMonth mes del fin del rango de busqueda
     *
     * @return array cuentas patrimoniales
     */
    public function filterAccounts($initDate, $endYear, $endMonth)
    {
        /* fecha inicial de busqueda */
        $initDate = $initDate . '-01';

        /* último día correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, $endMonth + 1, 1, $endYear) - 1));

        /* Fecha final de búsqueda */
        $endDate = $endYear . '-' . $endMonth . '-' . $endDay;

        /* Consulta de las cuentas con relación hacia asientos contables aprobados en un rango de fecha */
        $query = AccountingAccount::orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

        /* Cuentas patrimoniales en array asociativo */
        $arrAccounts = [];

        /* Se formatean los datos de las cuentas */
        array_push($arrAccounts, [
            'id' => 0,
            'text' => 'Seleccione...',
        ]);
        foreach ($query as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($query[$key]);
            }
        }
        foreach ($query as $account) {
            if ($account['entryAccount']) {
                array_push($arrAccounts, [
                    'text' => "{$account->getCodeAttribute()} - {$account->denomination}",
                    'id' => $account->id,
                ]);
            }
        }
        return $arrAccounts;
    }

    /**
     * Ruta para actualizar el listado de cuentas patrimoniales en un rango de fecha determinado
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos del rango de búsqueda de las cuentas
     *
     * @return JsonResponse cuentas patrimoniales
     */
    public function getAccAccount(Request $request)
    {
        $this->validate($request, [
            'initMonth' => ['required'],
            'initYear' => ['required'],
            'endMonth' => ['required'],
            'endYear' => ['required'],
        ]);

        return response()->json(['records' => $this->filterAccounts(
            $request->initYear . '-' . $request->initMonth,
            $request->endYear,
            $request->endMonth
        )]);
    }

    /**
     * Verifica las conversiones monetarias de un reporte de mayor analítico
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param string $initDate Rango de fecha inicial YYYY-mm
     * @param string $endDate Rango de fecha final YYYY-mm
     * @param string $initAcc  id de cuenta patrimonial inicial
     * @param string $endAcc   id de cuenta patrimonial final
     * @param Currency $currency Moneda en que se expresara el reporte
     *
     * @return JsonResponse
     */
    public function pdfVue($initDate, $endDate, $initAcc, $endAcc, Currency $currency)
    {
        $initDate = $initDate . '-01';

        /* Último día correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, explode('-', $endDate)[1] + 1, 1, explode('-', $endDate)[0]) - 1));

        /* Formatea la fecha final de búsqueda */
        $endDate = explode('-', $endDate)[0] . '-' . explode('-', $endDate)[1] . '-' . $endDay;

        if (isset($endAcc) && $endAcc < $initAcc) {
            $endAcc = (int) $endAcc;
            $aux = $initAcc;
            $initAcc = $endAcc;
            $endAcc = $aux;
        }

        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* Registros de las cuentas patrimoniales seleccionadas */
        $query = AccountingAccount::with(['entryAccount.entries' =>
            function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true);
                        }
                    }
                }
            }])
            ->whereBetween('id', [$initAcc, $endAcc])
            ->whereHas(
                'entryAccount.entries',
                function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                    } else {
                        if ($is_admin) {
                            $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true);
                        }
                    }
                }
            )->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC');

        $convertions = [];

        /* Se recorre y evalua la relacion en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            /* Indica si la fecha del asiento esta en el rango de alguna conversion */
            $cont = 0;
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions) &&
                        $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
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
                        (!$inRange || !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) &&
                        $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
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

        /* Enlace de consulta del reporte de mayor analítico */
        $url = 'analyticalMajor/' . $initDate . '/' . $endDate . '/' . $initAcc . '/' . $endAcc;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Registro del reporte del día si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Mayor Analítico')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Mayor Analítico',
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
     * Verifica las conversiones monetarias de un reporte de mayor analítico
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param string $initDate   Rango de fecha inicial YYYY-mm
     * @param string $endDate    Rango de fecha final YYYY-mm
     * @param string $initAcc    id de cuenta patrimonial inicial
     * @param string $endAcc     id de cuenta patrimonial final
     * @param Currency $currency moneda en que se expresara el reporte
     *
     * @return JsonResponse
     */
    public function pdfVueSign($initDate, $endDate, $initAcc, $endAcc, Currency $currency)
    {
        $initDate = $initDate . '-01';

        /* Último día correspondiente al mes */
        $endDay = date('d', (mktime(0, 0, 0, explode('-', $endDate)[1] + 1, 1, explode('-', $endDate)[0]) - 1));

        /* Formatea la fecha final de búsqueda */
        $endDate = explode('-', $endDate)[0] . '-' . explode('-', $endDate)[1] . '-' . $endDay;

        if (isset($endAcc) && $endAcc < $initAcc) {
            $endAcc = (int) $endAcc;
            $aux = $initAcc;
            $initAcc = $endAcc;
            $endAcc = $aux;
        }

        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* Registros de las cuentas patrimoniales seleccionadas */
        $query = AccountingAccount::with(['entryAccount.entries' =>
            function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true);
                        }
                    }
                }
            }])
            ->whereBetween('id', [$initAcc, $endAcc])
            ->whereHas(
                'entryAccount.entries',
                function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                    } else {
                        if ($is_admin) {
                            $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true);
                        }
                    }
                }
            )->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC');

        $convertions = [];

        /* Se recorre y evalua la relacion en las conversiones necesarias a realizar */
        foreach ($query as $record) {
            /* Indica si la fecha del asiento esta en el rango de alguna conversión */
            $cont = 0;
            foreach ($record['entryAccount'] as $entryAccount) {
                $inRange = false;
                if ($entryAccount['entries']) {
                    if (
                        !array_key_exists($entryAccount['entries']['currency']['id'], $convertions) &&
                        $entryAccount['entries']['currency']['id'] != $currency->id
                    ) {
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
                        (!$inRange || !array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) &&
                        $entryAccount['entries']['currency']['id'] != $currency['id']
                    ) {
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

        /* Enlace  para consultar ese regporte */
        $url = 'analyticalMajorSign/' . $initDate . '/' . $endDate . '/' . $initAcc . '/' . $endAcc;

        $currentDate = new DateTime();
        $currentDate = $currentDate->format('Y-m-d');

        /* Almacena el registro del reporte del día si existe */
        $report = AccountingReportHistory::whereBetween('updated_at', [
            $currentDate . ' 00:00:00',
            $currentDate . ' 23:59:59',
        ])
            ->where('report', 'Mayor Analítico')
            ->where('institution_id', $institution_id)->first();

        /* se crea o actualiza el registro del reporte */
        if (!$report) {
            $report = AccountingReportHistory::create(
                [
                    'report' => 'Mayor Analítico',
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
     * Método para comparar fechas
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return int
     */
    public function cm($a, $b)
    {
        $aDate = strtotime($a['entries']['created_at']);
        $bDate = strtotime($b['entries']['created_at']);
        return $aDate - $bDate;
    }

    /**
     * Genera el reporte en hoja de cálculo de Mayor Analítico
     *
     * @param  integer $report id de reporte y su información
     *
     * @return Renderable
     */
    public function export($report)
    {
        return  $this->pdf($report, true);
    }

    /**
     * Vista en la que se genera el reporte en pdf
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
     *
     * @return Renderable
     */
    public function pdf($report_id, $xml = false)
    {
        $report = AccountingReportHistory::with('currency')->find($report_id);

        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        $initDate = explode('/', $report->url)[1];
        $endDate = explode('/', $report->url)[2];
        $initAcc = explode('/', $report->url)[3];
        $endAcc = explode('/', $report->url)[4];

        $currency = $report->currency;

        if (isset($endAcc) && $endAcc < $initAcc) {
            $endAcc = (int) $endAcc;
            $aux = $initAcc;
            $initAcc = $endAcc;
            $endAcc = $aux;
        }
        $initDateYear = (int) explode('-', $initDate)[0] - 1;
        $fiscalYear = FiscalYear::where(['active' => false, 'closed' => true, 'year' => $initDateYear])
            ->orderBy('year', 'desc')
            ->first();
        $initGroup = AccountingAccount::find($initAcc);
        $endGroup = AccountingAccount::find($endAcc);
        $initGroupFormattedCode = $initGroup->group . $initGroup->subgroup .
        $initGroup->item . $initGroup->generic . $initGroup->specific .
        $initGroup->subspecific . $initGroup->institutional;

        $endGroupFormattedCode = $endGroup->group . $endGroup->subgroup .
        $endGroup->item . $endGroup->generic . $endGroup->specific .
        $endGroup->subspecific . $endGroup->institutional;
        if ($initGroupFormattedCode > $endGroupFormattedCode) {
            $aux = $endGroupFormattedCode;
            $endGroupFormattedCode = $initGroupFormattedCode;
            $initGroupFormattedCode = $aux;
        }

        if (isset($fiscalYear)) {
            /* @todo Si es enero devuelvo balance del año fiscal */

            if (date('m', strtotime($initDate)) > 1) {
                $fiscalYear = $fiscalYear->year;
                $initDate2 = $fiscalYear . "-1-01";
                $endDate2 = $fiscalYear . "-12-31";
                $last_Balance = $this->lastBalance($initDate, $endDate2, $initGroupFormattedCode, $endGroupFormattedCode, $currency);
                $typeBalance = 'fiscal';
                /* Si init Date no es enero devuelvo el balance del mes anterior */
            } else {
                $fecha = Carbon::createFromFormat('Y-m-d', $initDate);
                $mesAnterior = $fecha->subMonth();
                $initDate2 = $mesAnterior->copy()->startOfMonth();
                $endDate2 = $mesAnterior->copy()->endOfMonth();
                $last_Balance = $this->lastBalance($initDate, $endDate2, $initGroupFormattedCode, $endGroupFormattedCode, $currency);
                $typeBalance = 'month';
            }
        } else {
            $fecha = Carbon::createFromFormat('Y-m-d', $initDate);
            $mesAnterior = $fecha->subMonth();
            $initDate2 = $mesAnterior->copy()->startOfMonth();
            $endDate2 = $mesAnterior->copy()->endOfMonth();
            $last_Balance = $this->lastBalance($initDate, $endDate2, $initGroupFormattedCode, $endGroupFormattedCode, $currency);
            $typeBalance = 'month';
        }
        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }
        /* Registros de las cuentas patrimoniales seleccionadas */
        $query = AccountingAccount::with(['entryAccount.entries' =>
            function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id)->orderBy('from_date', 'ASC');
                    }
                } else {
                    if ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)) {
                            $query->whereBetween(
                                'from_date',
                                [$initDate, $endDate]
                            )
                                ->where('approved', true)->orderBy('from_date', 'desc');
                        }
                    }
                }
                $query->orderBy('from_date', 'asc');
            }])
            ->orderBy('group', 'asc')
            ->orderBy('subgroup', 'asc')
            ->orderBy('item', 'asc')
            ->orderBy('generic', 'asc')
            ->orderBy('specific', 'asc')
            ->orderBy('subspecific', 'asc')
            ->orderBy('denomination', 'asc')
            ->orderBy('institutional', 'asc')
            ->get()
            ->filter(function ($account) use ($initGroupFormattedCode, $endGroupFormattedCode) {
                $accountFormattedCode = $account->group . $account->subgroup .
                $account->item . $account->generic . $account->specific .
                $account->subspecific . $account->institutional;
                if ($accountFormattedCode >= $initGroupFormattedCode && $accountFormattedCode <= $endGroupFormattedCode) {
                    return $account;
                }
            });

        $convertions = [];
        $records = [];
        $sorted = [];

        /* Recorrido y formateo de informacion en arreglos para mostrar en pdf */
        foreach ($query as $record) {
            $acc = [
                'denomination' => $record['denomination'],
                'code' => $record['group'] . "." . $record['subgroup'] . "."
                . $record['item'] . "." . $record['generic'] . "."
                . $record['specific'] . "." . $record['subspecific'] . "."
                . $record['institutional'],
                'balance' => "0.00",
                'entryAccount' => [],
            ];

            foreach ($record['entryAccount'] as $entryAccount) {
                if ($entryAccount['entries']) {
                    $from_date = $entryAccount['entries']['from_date'];
                    $r = [
                        'debit' => '0',
                        'assets' => '0',
                        'entries' => [
                            'reference' => $entryAccount['entries']['reference'],
                            'concept' => $entryAccount['entries']['concept'],
                            'created_at' => $from_date,
                        ],
                    ];

                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
                        );
                    }

                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                    $this->calculateOperation(
                        $convertions,
                        $entryAccount['entries']['currency']['id'],
                        $entryAccount['debit'],
                        $entryAccount['entries']['from_date'],
                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                    ) : 0;

                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                    $this->calculateOperation(
                        $convertions,
                        $entryAccount['entries']['currency']['id'],
                        $entryAccount['assets'],
                        $entryAccount['entries']['from_date'],
                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                    ) : 0;

                    array_push($acc['entryAccount'], $r);
                }
            }
            usort($acc['entryAccount'], array($this, 'cm'));
            $records[$acc["code"]] = $acc;
        }
        foreach ($records as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($records[$key]);
            }
        }

        foreach ($last_Balance as $key => $b) {
            if (!array_key_exists($b["code"], $records)) {
                $acc = [
                    'denomination' => $b['denomination'],
                    'code' => $b["code"],
                    'balance' => $b["entryAccount"][0]["total"],
                    'entryAccount' => [0 => [
                        'debit' => '0',
                        'assets' => '0',
                        'entries' => [
                            'reference' => "",
                            'concept' => "sin conceptos",
                            'created_at' => $initDate,
                        ],

                    ],
                    ],
                ];
                $records[$b["code"]] = $acc;
            } else {
                $records[$b["code"]]["balance"] = $b["entryAccount"][0]["total"];
            }
        }

        /* Configuración general de la apliación */
        $setting = Setting::all()->first();

        $initDate = new DateTime($initDate);
        $endDate = new DateTime($endDate);

        $initDate = $initDate->format('d/m/Y');
        $endDate = $endDate->format('d/m/Y');

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        if ($xml) {
            return Excel::download(new AccountingAnalyticalMajorSheetExport([
                'pdf' => $pdf,
                'records' => $records,
                'typeBalance' => $typeBalance,
                'initDate' => $initDate,
                'endDate' => $endDate,
                'currency' => $currency,

            ]), now()->format('d-m-Y') . '_Reporte_de_mayor_analitico.xlsx');
        } else {
            /* Definición de las características generales de la página pdf */
            $institution = Institution::find(1);
            $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/analyticalMajor/' . $report->id)]);
            $pdf->setHeader('Reporte de Contabilidad', 'Reporte de mayor analítico');
            $pdf->setFooter();
            $pdf->setBody('accounting::pdf.analytical_major', true, [
                'pdf' => $pdf,
                'records' => $records,
                'typeBalance' => $typeBalance,
                'initDate' => $initDate,
                'endDate' => $endDate,
                'currency' => $currency,
            ]);
        }
    }

    /**
     * Cálculo de saldo anterior
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  string $initDate fecha inicial para iniciar la búsqueda, formato(YYYY-mm)
     * @param  string $endDate fecha final para iniciar la búsqueda, formato(YYYY-mm)
     * @param  integer $initAcc id de cuenta patrimonial inicial
     * @param  integer $endAcc id de cuenta patrimonial final
     * @param  Currency $currency Moneda en que se expresara el reporte
     *
     * @return array Devuelve un arreglo con información del sldo anterior
     */
    public function lastBalance($initDate, $endDate, $initAcc, $endAcc, $currency)
    {
        $institution_id = null;
        $is_admin = auth()->user()->isAdmin();
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }
        $results = AccountingAccount::select(
            'accounting_accounts.denomination',
            'accounting_accounts.id',
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
            DB::raw(
                "CONCAT(
                    accounting_accounts.group,
                    accounting_accounts.subgroup,
                    accounting_accounts.item,
                    accounting_accounts.generic,
                    accounting_accounts.specific,
                    accounting_accounts.subspecific,
                    accounting_accounts.institutional
                ) AS code_filter"
            ),
            'accounting_entries.reference',
            'accounting_entries.concept',
            'accounting_entries.id as entry_id',
            'accounting_entries.from_date',
            DB::raw(
                "CASE
                    WHEN accounting_accounts.group IN ('1', '4', '6') THEN
                        (SELECT SUM(debit - assets) FROM accounting_entry_accounts WHERE accounting_entry_accounts.accounting_account_id = accounting_accounts.id AND accounting_entry_accounts.accounting_entry_id = accounting_entries.id AND accounting_entry_accounts.deleted_at IS NULL)
                    WHEN accounting_accounts.group IN ('2', '3', '5') THEN
                        (SELECT SUM(assets - debit) FROM accounting_entry_accounts WHERE accounting_entry_accounts.accounting_account_id = accounting_accounts.id AND accounting_entry_accounts.accounting_entry_id = accounting_entries.id AND accounting_entry_accounts.deleted_at IS NULL)
                    ELSE 0
                END AS total"
            )
        )
            ->leftJoin('accounting_entry_accounts', 'accounting_accounts.id', '=', 'accounting_entry_accounts.accounting_account_id')
            ->leftJoin('accounting_entries', 'accounting_entry_accounts.accounting_entry_id', '=', 'accounting_entries.id')
            ->where(function ($query) use ($initDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->where('accounting_entries.from_date', '<', $initDate)
                        ->where('accounting_entries.approved', true)
                        ->where('accounting_entries.institution_id', $institution_id)
                    ) {
                        $query->where('accounting_entries.from_date', '<', $initDate)
                            ->where('accounting_entries.approved', true)
                            ->where('accounting_entries.institution_id', $institution_id)->where('accounting_entries.deleted_at', null);
                    }
                } else {
                    if ($is_admin) {
                        if (
                            $query->where('accounting_entries.from_date', '<', $initDate)
                            ->where('accounting_entries.approved', true)
                        ) {
                            $query->where('accounting_entries.from_date', '<', $initDate)
                                ->where('accounting_entries.approved', true)->where('accounting_entries.deleted_at', null);
                        }
                    }
                }
            })
            ->whereRaw('accounting_entry_accounts.deleted_at IS NULL')
            ->groupBy('accounting_accounts.denomination', 'accounting_entries.id', 'accounting_accounts.id', 'accounting_accounts.group', 'accounting_accounts.subgroup', 'accounting_accounts.item', 'accounting_accounts.generic', 'accounting_accounts.specific', 'accounting_accounts.subspecific', 'accounting_accounts.institutional', 'accounting_entries.reference', 'accounting_entries.concept', 'accounting_entries.from_date')
            ->orderBy('accounting_entries.from_date', 'ASC')
            ->get()->filter(function ($account) use ($initAcc, $endAcc) {
                $accountFormattedCode = $account->code_filter;
                if ($accountFormattedCode >= $initAcc && $accountFormattedCode <= $endAcc) {
                    return $account;
                }
            });
        $records = [];
        foreach ($results as $entry) {
            if (!array_key_exists($entry->code_account, $records)) {
                $acc = [
                    'denomination' => $entry->denomination,
                    'code' => $entry->code_account,
                    'balance' => $entry->total,
                    'entryAccount' => [0 => [
                        'total' => $entry->total,
                    ],
                    ],
                ];
                $records[$entry->code_account] = $acc;
            } else {
                $records[$entry->code_account]["balance"] += $entry->total;
                $records[$entry->code_account]["entryAccount"][0]["total"] += $entry->total;
            }
        }
        foreach ($records as $key => $a) {
            $b = explode('.', $a["code"]);
            if ($b[6] == 000) {
                unset($records[$key]);
            }
        }
        return $records;
    }

    /**
     * Vista en la que se genera el reporte en pdf
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $report id de reporte y su información
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

        $initDate = explode('/', $report->url)[1];
        $endDate = explode('/', $report->url)[2];
        $initAcc = explode('/', $report->url)[3];
        $endAcc = explode('/', $report->url)[4];

        $currency = $report->currency;

        if (isset($endAcc) && $endAcc < $initAcc) {
            $endAcc = (int) $endAcc;
            $aux = $initAcc;
            $initAcc = $endAcc;
            $endAcc = $aux;
        }

        $institution_id = null;

        $is_admin = auth()->user()->isAdmin();
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        if (!$is_admin && $user_profile['institution']) {
            $institution_id = $user_profile['institution']['id'];
        }

        /* Registros de las cuentas patrimoniales seleccionadas */
        $query = AccountingAccount::with(['entryAccount.entries' =>
            function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                if ($institution_id) {
                    if (
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                        ->where('institution_id', $institution_id)
                    ) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                    }
                } else {
                    if ($is_admin) {
                        if ($query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)) {
                            $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true);
                        }
                    }
                }
            }])
            ->whereBetween('id', [$initAcc, $endAcc])
            ->whereHas(
                'entryAccount.entries',
                function ($query) use ($initDate, $endDate, $institution_id, $is_admin) {
                    if ($institution_id) {
                        $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true)
                            ->where('institution_id', $institution_id);
                    } else {
                        if ($is_admin) {
                            $query->whereBetween('from_date', [$initDate, $endDate])->where('approved', true);
                        }
                    }
                }
            )->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')->get();

        $convertions = [];
        $records = [];

        /* Recorrido y formateo de información en arreglos para mostrar en pdf */
        foreach ($query as $record) {
            $acc = [
                'denomination' => $record['denomination'],
                'entryAccount' => [],
            ];
            foreach ($record['entryAccount'] as $entryAccount) {
                if ($entryAccount['entries']) {
                    $from_date = explode('-', $entryAccount['entries']['from_date'])[2] . '/' .
                    explode('-', $entryAccount['entries']['from_date'])[1] . '/' .
                    explode('-', $entryAccount['entries']['from_date'])[0];

                    $r = [
                        'debit' => '0',
                        'assets' => '0',
                        'entries' => [
                            'reference' => $entryAccount['entries']['reference'],
                            'concept' => $entryAccount['entries']['concept'],
                            'created_at' => $from_date,
                        ],
                    ];

                    if (!array_key_exists($entryAccount['entries']['currency']['id'], $convertions)) {
                        $convertions = $this->calculateExchangeRates(
                            $convertions,
                            $entryAccount['entries'],
                            $currency['id']
                        );
                    }

                    $r['debit'] = ($entryAccount['debit'] != 0) ?
                    $this->calculateOperation(
                        $convertions,
                        $entryAccount['entries']['currency']['id'],
                        $entryAccount['debit'],
                        $entryAccount['entries']['from_date'],
                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                    ) : 0;

                    $r['assets'] = ($entryAccount['assets'] != 0) ?
                    $this->calculateOperation(
                        $convertions,
                        $entryAccount['entries']['currency']['id'],
                        $entryAccount['assets'],
                        $entryAccount['entries']['from_date'],
                        ($entryAccount['entries']['currency']['id'] == $currency['id']) ?? false
                    ) : 0;

                    array_push($acc['entryAccount'], $r);
                }
            }
            array_push($records, $acc);
        }

        /* Configuración general de la apliación */
        $setting = Setting::all()->first();

        $initDate = new DateTime($initDate);
        $endDate = new DateTime($endDate);

        $initDate = $initDate->format('d/m/Y');
        $endDate = $endDate->format('d/m/Y');

        /* Base para generar el Reporte */
        $pdf = new ReportRepositorySign();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/analyticalMajor/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de mayor analítico');
        $pdf->setFooter();
        $sign = $pdf->setBody('accounting::pdf.analytical_major', true, [
            'pdf' => $pdf,
            'records' => $records,
            'initDate' => $initDate,
            'endDate' => $endDate,
            'currency' => $currency,
        ]);
        if ($sign['status'] == 'true') {
            return response()->download($sign['file'], $sign['filename'], [], 'inline');
        } else {
            return response()->json(['result' => $sign['status'], 'message' => $sign['message']], 200);
        }
    }

    /**
     * Realiza la conversión de saldo
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  array   $convertions   Lista de tipos cambios para la moneda
     * @param  integer $entry_id      Identificador del asiento
     * @param  float   $value         Saldo del asiento
     * @param  float   $date          Fecha del asiento
     * @param  boolean $equalCurrency Bandera que indica si el tipo de moneda en el que esta el asiento es las misma
     *                                que la que se desea expresar
     * @return float                  Devuelve el resultdado de la operación
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
     * @return array                        Devuelve la lista de conversiones actualizada
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
     * Verifica el salto de página
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }
}
