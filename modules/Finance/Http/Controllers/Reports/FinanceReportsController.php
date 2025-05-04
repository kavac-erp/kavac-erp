<?php

namespace Modules\Finance\Http\Controllers\Reports;

use App\Models\Currency;
use App\Models\Parameter;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Illuminate\Routing\Controller;
use App\Rules\DateBeforeFiscalYear;
use App\Repositories\ReportRepository;
use Modules\Finance\Models\FinancePayOrder;
use Modules\Finance\Models\FinanceBankingMovement;
use Modules\Finance\Models\FinancePaymentExecute;

/**
 * @class FinanceReportsController
 * @brief Clase que gestiona los reportes de finanzas, tanto de emisiones de pago, como de ordenes y de movimientos bancarios
 *
 * Clase que gestiona los reportes de finanzas, tanto de emisiones de pago, como de ordenes y de movimientos bancarios
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve | javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceReportsController extends Controller
{
    /**
     * Reglas de validación
     *
     * @var array $rules
     */
    protected $rules = [];

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages = [];

    /**
     * Año de ejercicio fiscal
     *
     * @var string $fiscal_year
     */
    protected $fiscal_year;

    /**
     * Información de la institución u organismo
     *
     * @var Institution $institution
     */
    protected $institution;

    /**
     * Información de la moneda
     *
     * @var Currency $currency
     */
    protected $currency;

    /**
     * Información de la cantidad de números decimales configurados en el sistema
     *
     * @var Parameter $number_decimals
     */
    protected $number_decimals;

    /**
     * Información del tipo de redondeo configurado en el sistema
     *
     * @var Parameter $round
     */
    protected $round;

    /**
     * Nombre de la función que realiza el redondeo
     *
     * @var string $nameDecimalFunction
     */
    protected $nameDecimalFunction;

    /**
     * Tipo de reporte a mostrar
     *
     * @var integer $report_type
     */
    protected $report_type;

    /**
     * Campos de fechas para generar el reporte
     *
     * @var array $reportDate
     */
    protected $reportDate = [];

    /**
     * Campos de montos para generar el reporte
     *
     * @var array $amountFields
     */
    protected $amountFields = [];

    /**
     * Método constructor de la clase.
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->rules = [
            'reportTypeId' => 'required',
            'dateIni' => ['required', 'date', 'before_or_equal:dateEnd', new DateBeforeFiscalYear('Fecha de inicio')],
            'dateEnd' => ['required', 'date', 'after_or_equal:dateIni', new DateBeforeFiscalYear('Fecha final')],
        ];
        $this->messages = [
            'reportTypeId.required' => 'El tipo de reporte es obligatorio',
            'dateIni.required' => 'La fecha de inicio es obligatoria',
            'dateIni.before_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha final',
            'dateEnd.required' => 'La fecha final es obligatoria',
            'dateEnd.after_or_equal' => 'La fecha final no puede ser anterior a la fecha de inicio',
        ];

        $this->institution = Institution::query()
            ->where([
                'active' => true,
            ])
            ->whereHas('fiscalYears', function ($q) {
                $q->where('active', true);
            })
            ->first();

        $this->fiscal_year = $this->institution->fiscalYears->first()->year;

        $this->currency = Currency::query()->where([
            'default' => true,
        ])->first();

        $this->number_decimals = Parameter::query()
            ->where([
                'p_key' => 'number_decimals',
            ])
            ->value('p_value');

        $this->round = Parameter::query()
            ->where([
                'p_key' => 'round',
            ])
            ->first();

        $this->nameDecimalFunction = $this->round->p_value == 'false' ? 'currency_format' : 'round';

        $this->report_type = $request->reportTypeId;

        $this->reportDate = [
            'order' => 'ordered_at',
            'execute' => 'paid_at',
        ];

        $this->amountFields = [
            'order' => 'amount',
            'execute' => 'paid_amount',
        ];
    }

    /**
     * Muestra el formulario para generar el reporte
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('finance::reports.general-finance-reports');
    }

    /**
     * Obtiene el listado de estatus de documentos
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getDocumentStatusList()
    {
        $documentStatus = DocumentStatus::query()
            ->whereIn('action', ['AN', 'AP', 'PR'])
            ->get(['name', 'action']);

        $records[] = [
            'id' => "",
            'text' => 'Seleccione...'
        ];

        $documentStatus->each(function ($status) use (&$records) {
            $records[] = [
                'id' => $status->action,
                'text' => $status->name
            ];
        });

        return response()->json(['records' => $records], 200);
    }

    /**
     * Genera el reporte de emisiones de pagos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return void
     */
    public function pdfPaymentExecutes(Request $request)
    {
        $request->validate($this->rules, $this->messages);

        $dateIni    = $request->dateIni;
        $dateEnd    = $request->dateEnd;
        $action     = $request->action;
        $receiverId = $request->receiverId;

        $reportData = FinancePaymentExecute::query()
            ->when($dateIni, function ($query) use ($dateIni) {
                $query->where('paid_at', '>=', $dateIni);
            })
            ->when($dateEnd, function ($query) use ($dateEnd) {
                $query->where('paid_at', '<=', $dateEnd);
            })
            ->when($action, function ($query) use ($action) {
                $query->whereHas('documentStatus', function ($q) use ($action) {
                    $q->where('action', $action);
                });
            })->with(['financePayOrders', 'documentStatus'])
            ->orderBy('paid_at', 'asc')
            ->get()
            ->when($receiverId, function ($query) use ($receiverId) {
                return $query->filter(function ($q) use ($receiverId) {
                    return $q->receiver_id == $receiverId;
                });
            });

        if (!$reportData->count()) {
            return response()->json([
                'data' => null,
                'success' => false,
                'message' => 'No se encontraron registros',
            ], 200);
        }

        $institution = $this->institution;

        $fiscal_year = $this->fiscal_year;

        $currency = $this->currency;

        $number_decimals = $this->number_decimals;

        $round = $this->round;

        $nameDecimalFunction = $this->nameDecimalFunction;

        $reportDate = $this->reportDate[$this->report_type];

        $amountField = $this->amountFields[$this->report_type];

        $report_type = $this->report_type;


        /* Base para generar el pdf*/
        $pdf = new ReportRepository();

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify' => url('/finance/payment-reports/payment-execute/pdf'),
            ]
        );

        $subHeader = 'Información de emisiones de pago desde '
            . date_create($dateIni)->format('d/m/Y')
            . ' hasta '
            . date_create($dateEnd)->format('d/m/Y');

        $pdf->setHeader(
            "Reporte de Emisiones de Pago",
            $subHeader,
            true,
            false,
            '',
            'C',
            'C'
        );

        $pdf->setFooter();

        $pdf->setBody(
            'finance::pdf.pay_order_and_payment_execute.report',
            true,
            compact(
                'reportData',
                'institution',
                'currency',
                'number_decimals',
                'nameDecimalFunction',
                'fiscal_year',
                'report_type',
                'reportDate',
                'amountField'
            )
        );
    }

    /**
     * Genera el reporte de ordenes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return void
     */
    public function pdfPayOrders(Request $request)
    {
        $request->validate($this->rules, $this->messages);

        $dateIni    = $request->dateIni;
        $dateEnd    = $request->dateEnd;
        $action     = $request->action;
        $receiverId = $request->receiverId;

        $reportData = FinancePayOrder::query()
            ->when($dateIni, function ($query) use ($dateIni) {
                $query->where('ordered_at', '>=', $dateIni);
            })
            ->when($dateEnd, function ($query) use ($dateEnd) {
                $query->where('ordered_at', '<=', $dateEnd);
            })
            ->when($action, function ($query) use ($action) {
                $query->whereHas('documentStatus', function ($q) use ($action) {
                    $q->where('action', $action);
                });
            })
            ->orderBy('ordered_at', 'asc')
            ->get()
            ->when($receiverId, function ($query) use ($receiverId) {
                return $query->filter(function ($q) use ($receiverId) {
                    return $q->ReceiverId() == $receiverId;
                });
            });

        $institution = $this->institution;
        $fiscal_year = $this->fiscal_year;
        $currency = $this->currency;
        $number_decimals = $this->number_decimals;
        $round = $this->round;
        $nameDecimalFunction = $this->nameDecimalFunction;
        $reportDate = $this->reportDate[$this->report_type];
        $report_type = $this->report_type;
        $amountField = $this->amountFields[$this->report_type];

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify' => url('/finance/payment-reports/pay-order/pdf'),
            ]
        );

        $subHeader = 'Información de emisiones de pago desde '
            . date_create($dateIni)->format('d/m/Y')
            . ' hasta '
            . date_create($dateEnd)->format('d/m/Y');

        $pdf->setHeader(
            "Reporte de Órdenes de Pago",
            $subHeader,
            true,
            false,
            '',
            'C',
            'C'
        );

        $pdf->setFooter();

        $pdf->setBody(
            'finance::pdf.pay_order_and_payment_execute.report',
            true,
            compact(
                'reportData',
                'institution',
                'currency',
                'number_decimals',
                'nameDecimalFunction',
                'fiscal_year',
                'report_type',
                'reportDate',
                'amountField'
            )
        );
    }

    /**
     * Genera el reporte de movimientos bancarios
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return void
     */
    public function pdfBankingMovements(Request $request)
    {
        $request->validate($this->rules, $this->messages);

        $dateIni    = $request->dateIni;
        $dateEnd    = $request->dateEnd;
        $action     = $request->action;

        $reportData = FinanceBankingMovement::query()
            ->when($dateIni, function ($query) use ($dateIni) {
                $query->where('payment_date', '>=', $dateIni);
            })
            ->when($dateEnd, function ($query) use ($dateEnd) {
                $query->where('payment_date', '<=', $dateEnd);
            })
            ->when($action, function ($query) use ($action) {
                $query->whereHas('documentStatus', function ($q) use ($action) {
                    $q->where('action', $action);
                });
            })
            ->with(['financeBankAccount.financeBankingAgency.financeBank', 'documentStatus'])
            ->orderBy('payment_date', 'asc')
            ->get();

        $institution = $this->institution;

        $fiscal_year = $this->fiscal_year;

        $currency = $this->currency;

        $number_decimals = $this->number_decimals;

        $round = $this->round;

        $nameDecimalFunction = $this->nameDecimalFunction;

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify' => url('/finance/payment-reports/pay-order/pdf'),
                'orientation' => 'L',
            ]
        );

        $subHeader = $request->has('all') ? 'Información de movimientos bancarios del año ' . $fiscal_year : 'Información de movimientos bancarios desde '
            . date_create($dateIni)->format('d/m/Y')
            . ' hasta '
            . date_create($dateEnd)->format('d/m/Y');

        $pdf->setHeader(
            "Reporte de Movimientos Bancarios",
            $subHeader,
            true,
            false,
            '',
            'C',
            'C'
        );

        $pdf->setFooter();

        $pdf->setBody(
            'finance::pdf.banking_movements.report',
            true,
            compact(
                'reportData',
                'institution',
                'currency',
                'number_decimals',
                'nameDecimalFunction',
                'fiscal_year',
            )
        );
    }
}
