<?php

/**
 * Controlador de libro diario en contabilidad
 */

namespace Modules\Accounting\Http\Controllers\Reports;

use Illuminate\Routing\Controller;
use Modules\Accounting\Models\AccountingReportHistory;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\Currency;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Institution;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\ExchangeRate;
use App\Repositories\ReportRepository;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;
use Auth;
use DateTime;

/**
 *  * Clase que gestiona el reporte de libro diario
 *
 * @class AccountingReportPdfDailyBookController
 *
 * @brief Controlador para la generación del reporte del libro diario
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve>
 *
 * @license LICENCIA DE SOFTWARE CENDITEL
 * @link    http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/
 */
class AccountingDailyBookController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
     */
    public function __construct()
    {
        /**
         * Establece permisos de acceso para cada método del controlador
         * */
        $this->middleware(
            'permission:accounting.report.dailybook',
            ['only' => ['index', 'pdf', 'pdfVue', 'pdfSign', 'pdfVueSign']]
        );
    }

    protected $records = [];

    /**
     * Función para obtener records
     *
     * @return array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Función para mezclar records
     *
     * @param array $records Records
     *
     * @return null
     */
    public function setRecords($records)
    {
        $this->records = array_merge($this->records, $records);
    }
    /**
     * [pdf verifica las conversiones monetarias de un reporte libro diario]
     *
     * @param string   $initDate variable con la fecha inicial
     * @param string   $endDate  variable con la fecha inicial
     * @param Currency $currency moneda en que se expresara el reporte
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pdfVue($initDate, $endDate, Currency $currency)
    {
        /**
         * [$entries información del asiento contable]
         *
         * @var AccountingEntry
         */
        $entries = [];
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (auth()->user()->isAdmin()) {
            $entries = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        } elseif ($user_profile['institution']['id']) {
            $entries = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->where('institution_id', $user_profile['institution']['id'])
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        }

        $convertions = [];

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        foreach ($entries as $entry) {
            $inRange = false;
            if (
                !array_key_exists($entry['currency']['id'], $convertions) && $entry['currency']['id'] != $currency->id
            ) {
                $convertions = $this->calculateExchangeRates($convertions, $entry, $currency->id);

                foreach ($convertions as $convertion) {
                    foreach ($convertion as $convert) {
                        if (
                            $entry['from_date'] >= $convert['start_at'] && $entry['from_date'] <= $convert['end_at']
                        ) {
                            $inRange = true;
                        }
                    }
                }
                if (
                    !$inRange || (!array_key_exists($entry['currency']['id'], $convertions)
                    && $entry['currency']['id'] != $currency['id'])
                ) {
                    return response()->json(
                        [
                        'result' => false,
                        'message' => 'Imposible expresar asiento contable ' . $entry['reference']
                        . ' de ' . $entry['currency']['symbol']
                        . ' (' . $entry['currency']['name'] . ')'
                        . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                        ', verificar tipos de cambio configurados. Para la fecha de ' .
                        $entry['from_date'],
                        ],
                        200
                    );
                }
            }
        }

        $dataInRange = $entries->get()->toArray();
        if ($dataInRange == []) {
            return response()->json(
                [
                'result' => false,
                'message' => 'No se ha encontrado ningún registro entre el rango de fechas establecido.',
                ],
                200
            );
        }

        /**
         * [$url link para consultar ese regporte]
         *
         * @var string
         */
        $url = 'dailyBook/' . $initDate . '/' . $endDate;


        if (auth()->user()->isAdmin()) {
            /**
             * [$report almacena el registro del reporte del dia si existe]
             *
             * @var [type]
             */
            $report = AccountingReportHistory::whereBetween(
                'updated_at',
                [
                $initDate . ' 00:00:00',
                $endDate . ' 23:59:59'
                ]
            )
                ->where('report', 'Libro Diario')->first();
        } else {
            /**
             * [$report almacena el registro del reporte del dia si existe]
             *
             * @var [type]
             */
            $report = AccountingReportHistory::whereBetween(
                'updated_at',
                [
                $initDate . ' 00:00:00',
                $endDate . ' 23:59:59'
                ]
            )
                ->where('report', 'Libro Diario')
                ->where('institution_id', $user_profile['institution']['id'])->first();
        }

        /*
         * se crea o actualiza el registro del reporte
         */

        if (auth()->user()->isAdmin()) {
            if (!$report) {
                $report = AccountingReportHistory::create(
                    [
                        'report' => 'Libro Diario',
                        'url' => $url,
                        'currency_id' => $currency->id,
                    ]
                );
            } else {
                $report->url = $url;
                $report->currency_id = $currency->id;
                $report->save();
            }
        } else {
            if (!$report) {
                $report = AccountingReportHistory::create(
                    [
                        'report' => 'Libro Diario',
                        'url' => $url,
                        'currency_id' => $currency->id,
                        'institution_id' => $user_profile['institution']['id'],
                    ]
                );
            } else {
                $report->url = $url;
                $report->currency_id = $currency->id;
                $report->institution_id = $user_profile['institution']['id'];
                $report->save();
            }
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }

    /**
     * [pdf verifica las conversiones monetarias de un reporte libro diario]
     *
     * @param string   $initDate variable con la fecha inicial
     * @param string   $endDate  variable con la fecha inicial
     * @param Currency $currency moneda en que se expresara el reporte
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
     *
     * @return null
     */
    public function pdfVueSign($initDate, $endDate, Currency $currency)
    {
        /**
         * [$entries información del asiento contable]
         *
         * @var AccountingEntry
         */
        $entries = [];
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (auth()->user()->isAdmin()) {
            $entries = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        } elseif ($user_profile['institution']['id']) {
            $entries = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->where('institution_id', $user_profile['institution']['id'])
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        }

        $convertions = [];

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        foreach ($entries as $entry) {
            $inRange = false;
            if (
                !array_key_exists($entry['currency']['id'], $convertions) && $entry['currency']['id'] != $currency->id
            ) {
                $convertions = $this->calculateExchangeRates($convertions, $entry, $currency->id);

                foreach ($convertions as $convertion) {
                    foreach ($convertion as $convert) {
                        if (
                            $entry['from_date'] >= $convert['start_at'] && $entry['from_date'] <= $convert['end_at']
                        ) {
                            $inRange = true;
                        }
                    }
                }
                if (
                    !$inRange || (!array_key_exists($entry['currency']['id'], $convertions) && $entry['currency']['id'] != $currency['id'])
                ) {
                    return response()->json(
                        [
                        'result' => false,
                        'message' => 'Imposible expresar asiento contable ' . $entry['reference']
                        . ' de ' . $entry['currency']['symbol']
                        . ' (' . $entry['currency']['name'] . ')'
                        . ' a ' . $currency['symbol'] . '(' . $currency['name'] . ')' .
                        ', verificar tipos de cambio configurados. Para la fecha de ' .
                        $entry['from_date'],
                        ],
                        200
                    );
                }
            }
        }
        /**
         * [$url link para consultar ese regporte]
         *
         * @var string
         */
        $url = 'dailyBookSign/' . $initDate . '/' . $endDate;


        if (auth()->user()->isAdmin()) {
            /**
             * [$report almacena el registro del reporte del dia si existe]
             *
             * @var [type]
             */
            $report = AccountingReportHistory::whereBetween(
                'updated_at',
                [
                $initDate . ' 00:00:00',
                $endDate . ' 23:59:59'
                ]
            )
                ->where('report', 'Libro Diario')->first();
        } else {
            /**
             * [$report almacena el registro del reporte del dia si existe]
             *
             * @var [type]
             */
            $report = AccountingReportHistory::whereBetween(
                'updated_at',
                [
                $initDate . ' 00:00:00',
                $endDate . ' 23:59:59'
                ]
            )
                ->where('report', 'Libro Diario')
                ->where('institution_id', $user_profile['institution']['id'])->first();
        }

        /*
         * se crea o actualiza el registro del reporte
         */

        if (auth()->user()->isAdmin()) {
            if (!$report) {
                $report = AccountingReportHistory::create(
                    [
                        'report' => 'Libro Diario',
                        'url' => $url,
                        'currency_id' => $currency->id,
                    ]
                );
            } else {
                $report->url = $url;
                $report->currency_id = $currency->id;
                $report->save();
            }
        } else {
            if (!$report) {
                $report = AccountingReportHistory::create(
                    [
                        'report' => 'Libro Diario',
                        'url' => $url,
                        'currency_id' => $currency->id,
                        'institution_id' => $user_profile['institution']['id'],
                    ]
                );
            } else {
                $report->url = $url;
                $report->currency_id = $currency->id;
                $report->institution_id = $user_profile['institution']['id'];
                $report->save();
            }
        }

        return response()->json(['result' => true, 'id' => $report->id], 200);
    }
    /**
     * [pdf vista en la que se genera el reporte en pdf del libro diario]
     *
     * @param integer $report_id [id de reporte y su informacion]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
     * @return null
     */
    public function pdf($report_id)
    {
        $report = AccountingReportHistory::with('currency')->find($report_id);
        // Validar acceso para el registro
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (!auth()->user()->isAdmin()) {
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }
        $initDate = explode('/', $report->url)[1];
        $endDate = explode('/', $report->url)[2];


        $currency = $report->currency;

        /**
         * [$entries información del asiento contable]
         *
         * @var AccountingEntry
         */
        $entries = '';

        $convertions = [];



        if (auth()->user()->isAdmin()) {
            $r = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        } elseif ($user_profile['institution']['id']) {
            $r = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->where('institution_id', $user_profile['institution']['id'])
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        }

        $r->chunk(
            250,
            function ($entries) use ($convertions, $currency) {
                $records = [];
                foreach ($entries as $entry) {
                    $entry['concept'] = strip_tags($entry['concept']);
                    $entry['concept'] = str_replace("&nbsp;", '', $entry['concept']);
                    $convertions = $this->calculateExchangeRates($convertions, $entry, $currency['id']);

                    $from_date = explode('-', $entry['from_date']);
                    $record = [
                        'id' => $entry['id'],
                        'from_date' => $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0],
                        'accountingAccounts' => [],
                    ];

                    $record['accountingAccounts'] = [];
                    foreach ($entry['accountingAccounts'] as $r) {
                        array_push(
                            $record['accountingAccounts'],
                            [
                            'debit' => ($r['debit'] != 0) ?
                            $this->calculateOperation(
                                $convertions,
                                $entry['currency']['id'],
                                $r['debit'],
                                $entry['from_date'],
                                ($entry['currency']['id'] != $currency->id) ?? true
                            ) : 0,
                            'assets' => ($r['assets'] != 0) ?
                            $this->calculateOperation(
                                $convertions,
                                $entry['currency']['id'],
                                $r['assets'],
                                $entry['from_date'],
                                ($entry['currency']['id'] != $currency->id) ?? true
                            ) : 0,
                            'code' => $r['account'] ? $r['account']['code'] : '',
                            'denomination' => $r['account'] ? $r['account']['denomination'] : '',
                            'concept' => $r['account'] ? $entry['concept'] : '',
                            ]
                        );
                    }
                    array_push($records, $record);
                }
                $this->setRecords($records);
            }
        );
        /**
         * [$setting configuración general de la apliación]
         *
         * @var Setting
         */
        $setting = Setting::all()->first();
        $initDate = new DateTime($initDate);
        $endDate = new DateTime($endDate);

        $initDate = $initDate->format('d/m/Y');
        $endDate = $endDate->format('d/m/Y');

        $Entry = false;

        /**
         * [$pdf base para generar el pdf]
         *
         * @var [Modules\Accounting\Pdf\Pdf]
         */
        $pdf = new ReportRepository();
        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        $institution = Institution::find(1);
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('report/auxiliaryBook/' . $report->id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de libro diario');
        $pdf->setFooter();
        $pdf->setBody(
            'accounting::pdf.entry_and_daily_book',
            true,
            [
            'pdf' => $pdf,
            'entries' => $this->getRecords(),
            'convertions' => $convertions,
            'currency' => $currency,
            'Entry' => $Entry,
            'initDate' => $initDate,
            'endDate' => $endDate,
            ]
        );
    }

    /**
     * [pdf vista en la que se genera el reporte en pdf del libro diario]
     *
     * @param integer $report_id [id de reporte y su informacion]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
     *
     * @return null
     */
    public function pdfSign($report_id)
    {
        $report = AccountingReportHistory::with('currency')->find($report_id);
        // Validar acceso para el registro
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (!auth()->user()->isAdmin()) {
            if ($report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }
        $initDate = explode('/', $report->url)[1];
        $endDate = explode('/', $report->url)[2];

        $currency = $report->currency;

        /**
         * [$entries información del asiento contable]
         *
         * @var AccountingEntry
         */
        $entries = '';

        $convertions = [];



        if (auth()->user()->isAdmin()) {
            $r = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        } elseif ($user_profile['institution']['id']) {
            $r = AccountingEntry::with(
                'accountingAccounts.account'
            )->where('approved', true)
                ->where('institution_id', $user_profile['institution']['id'])
                ->whereBetween("from_date", [$initDate, $endDate])
                ->orderBy('from_date', 'ASC');
        }

        $r->chunk(
            250,
            function ($entries) use ($convertions, $currency) {
                $records = [];
                foreach ($entries as $entry) {
                    $convertions = $this->calculateExchangeRates($convertions, $entry, $currency['id']);

                    $from_date = explode('-', $entry['from_date']);
                    $record = [
                        'id' => $entry['id'],
                        'from_date' => $from_date[2] . '-' . $from_date[1] . '-' . $from_date[0],
                        'accountingAccounts' => [],
                    ];

                    $record['accountingAccounts'] = [];
                    foreach ($entry['accountingAccounts'] as $r) {
                        array_push(
                            $record['accountingAccounts'],
                            [
                            'debit' => ($r['debit'] != 0) ?
                            $this->calculateOperation(
                                $convertions,
                                $entry['currency']['id'],
                                $r['debit'],
                                $entry['from_date'],
                                ($entry['currency']['id'] != $currency->id) ?? true
                            ) : 0,
                            'assets' => ($r['assets'] != 0) ?
                            $this->calculateOperation(
                                $convertions,
                                $entry['currency']['id'],
                                $r['assets'],
                                $entry['from_date'],
                                ($entry['currency']['id'] != $currency->id) ?? true
                            ) : 0,
                            'code' => $r['account']->getCodeAttribute(),
                            'denomination' => $r['account']['denomination']
                            ]
                        );
                    }
                    array_push($records, $record);
                }
                $this->setRecords($records);
            }
        );

        /**
         * [$setting configuración general de la apliación]
         *
         * @var Setting
         */
        $setting = Setting::all()->first();
        $initDate = new DateTime($initDate);
        $endDate = new DateTime($endDate);

        $initDate = $initDate->format('d/m/Y');
        $endDate = $endDate->format('d/m/Y');

        $Entry = false;

        /**
         * [$pdf base para generar el pdf]
         *
         * @var [Modules\Accounting\Pdf\Pdf]
         */
        $pdf = new ReportRepositorySign();
        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        $institution = Institution::find(1);
        $pdf->setConfig(
            [
            'institution' => $institution,
            'urlVerify' => url('report/auxiliaryBookSign/' . $report->id)
            ]
        );
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de libro diario');
        $pdf->setFooter();
        $sign = $pdf->setBody(
            'accounting::pdf.entry_and_daily_book',
            true,
            [
            'pdf' => $pdf,
            'entries' => $this->getRecords(),
            'convertions' => $convertions,
            'currency' => $currency,
            'Entry' => $Entry,
            'initDate' => $initDate,
            'endDate' => $endDate,
            ]
        );
        if ($sign['status'] == 'true') {
            return response()->download($sign['file'], $sign['filename'], [], 'inline');
        } else {
            return response()->json(['result' => $sign['status'], 'message' => $sign['message']], 200);
        }
    }

    /**
     * [calculateOperation realiza la conversion de saldo]

     * @param array   $convertions   [lista de tipos cambios para la moneda]
     * @param integer $currency_id   [identificador del asiento]
     * @param float   $value         [saldo del asiento]
     * @param float   $date          [fecha del asiento]
     * @param boolean $equalCurrency [bandera que indica si el tipo de moneda
     *                               en el que esta el asiento es la misma
     *                               que la que se desea expresar]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
     *
     * @return float                  [resultdado de la operacion]
     */
    public function calculateOperation($convertions, $currency_id, $value, $date, $equalCurrency)
    {
        if (!$equalCurrency) {
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
     * [calculateExchangeRates encuentra los tipos de cambio]
     *
     * @param array           $convertions [lista de conversiones]
     * @param AccountingEntry $entry       [asiento contable]
     * @param integer         $currency_id [identificador de la moneda
     *                                     a la cual se realizara la conversion]
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve>
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
                            'amount' => $recordExchangeRate->amount,
                            'operator' => ($currency_id == $recordExchangeRate->from_currency_id) ? 'from' : 'to',
                            'start_at' => $recordExchangeRate->start_at,
                            'end_at' => $recordExchangeRate->end_at
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
     * @return array
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }
}
