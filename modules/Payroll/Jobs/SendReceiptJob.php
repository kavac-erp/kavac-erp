<?php

namespace Modules\Payroll\Jobs;

use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Parameter;
use App\Mail\SendReceipts;
use App\Models\Institution;
use Illuminate\Bus\Queueable;
use App\Repositories\ReportRepository;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Jobs\PayrollSendReceiptsEmailJob;
use Modules\Payroll\Actions\GetPayrollConceptParameters;
use Modules\Payroll\Actions\PayrollPaymentRelationshipAction;

/**
 * @class SendReceiptJob
 * @brief Instrucciones para enviar recibos de pago de nómina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SendReceiptJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo.
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Listado de meses del año
     *
     * @var array $months
     */
    protected $months = [
        'January'   => 'Enero',
        'February'  => 'Febrero',
        'March'     => 'Marzo',
        'April'     => 'Abril',
        'May'       => 'Mayo',
        'June'      => 'Junio',
        'July'      => 'Julio',
        'August'    => 'Agosto',
        'September' => 'Septiembre',
        'October'   => 'Octubre',
        'November'  => 'Noviembre',
        'December'  => 'Diciembre',
    ];

    /**
     * Mes del recibo de pago
     *
     * @var string $month
     */
    protected $month;

    /**
     * Año del recibo de pago
     *
     * @var string $year
     */
    protected $year;

    /**
     * Monto total del recibo de pago
     *
     * @var float $total
     */
    protected $total;

    /**
     * Monto total de asignaciones
     *
     * @var float $assignations_total
     */
    protected $assignations_total;

    /**
     * Monto total de deducciones
     *
     * @var float $deductions_total
     */
    protected $deductions_total;

    /**
     * Monto total actual
     *
     * @var float $current_total
     */
    protected $current_total;

    /**
     * Establece si la nómina tiene parámetros
     *
     * @var boolean $has_params
     */
    protected $has_params = false;


    /**
     * Método constructor de la clase
     *
     * @param integer $payrollId ID de la nómina
     * @param integer $institutionId ID de la institución
     * @param Parameter $number_decimals Número de decimales a mostrar
     * @param Parameter $round Función de redondeo a usar
     *
     * @return void
     */
    public function __construct(
        protected int $payrollId,
        protected int $institutionId,
        protected ?Parameter $number_decimals = null,
        protected ?Parameter $round = null,
    ) {
        $this->number_decimals = Parameter::query()
            ->where([
                'p_key' => 'number_decimals',
                'required_by' => 'payroll',
            ])
            ->first();

        $this->round = Parameter::query()
            ->where([
                'p_key' => 'round',
                'required_by' => 'payroll',
            ])
            ->first();
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        $institution = Institution::find($this->institutionId);
        $currency = Currency::query()
            ->where('default', true)
            ->first();

        $nameDecimalFunction = $this->round->p_value == 'false' ? 'currency_format' : 'round';
        $payrollPaymentRelationshipAction = new PayrollPaymentRelationshipAction();
        $timeParameters = [];
        array_map(function ($payrollParameter) use (&$timeParameters) {
            $timeParameters[$payrollParameter['staff_id']][] = $payrollParameter;
        }, $payrollPaymentRelationshipAction->getPayrollParameters($this->payrollId, false));

        $payrollStaffPayroll = PayrollStaffPayroll::query()
            ->with(["payroll" => function ($query) {
                $query->with(["payrollPaymentPeriod" => function ($query) {
                    $query->select('id', 'start_date', 'end_date');
                }]);
            }])
            ->with(["payrollStaff" => function ($query) {
                $query->without([
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollGender',
                    'payrollBloodType',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollStaffUniformSize',
                    'payrollSocioeconomic',
                    'payrollProfessional',
                    'payrollResponsibility'
                ]);
            }])
            ->where('payroll_id', $this->payrollId)
            ->get()
            ->toArray();

        $startDate = $payrollStaffPayroll[0]['payroll']['payroll_payment_period']['start_date'];
        $endDate = $payrollStaffPayroll[0]['payroll']['payroll_payment_period']['end_date'];
        $this->month = Carbon::createFromFormat('Y-m-d', $startDate)->format('F');
        $this->month = $this->months[$this->month];
        $this->year = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y');

        $params = (new GetPayrollConceptParameters());
        $payrollPersonalConceptAssign = [];

        foreach ($payrollStaffPayroll as &$payrollStaff) {
            $this->has_params = count($timeParameters[$payrollStaff['payroll_staff_id']] ?? []) > 0;
            $this->total = 0.0;
            $this->assignations_total = 0.0;
            $this->deductions_total = 0.0;

            foreach ($payrollStaff['concept_type'] as $concept_name => &$concept_type_arr) {
                $this->current_total = 0.0;
                foreach ($concept_type_arr as $index => &$concept_type) {
                    if (doubleval($concept_type['value']) > 0) {
                        if ($concept_type["sign"] == '+') {
                            /* Listado de trabajadores a los que aplica un concepto */
                            if (!isset($payrollPersonalConceptAssign[$concept_type["id"]])) {
                                $payrollPersonalConceptAssign[$concept_type["id"]] =
                                    $params->getPayrollPersonalConceptAssign(
                                        $concept_type["id"],
                                        $payrollStaff["payroll_id"],
                                        true
                                    );
                            }
                            if (count($timeParameters)) {
                                $concept_type["parameters"] = array_map(function ($parameter) use ($timeParameters, $payrollStaff, $concept_type) {
                                    $filter = array_filter($timeParameters[$payrollStaff['payroll_staff_id']], function ($timeParameter) use ($parameter, $concept_type) {
                                        return ($timeParameter["id"] == $parameter["id"]) && ($timeParameter["time_sheet"] == $concept_type["time_sheet"]);
                                    });
                                    return (count($filter) > 0) ? array(reset($filter)) : [];
                                }, isset($payrollPersonalConceptAssign[$concept_type["id"]]) ? $payrollPersonalConceptAssign[$concept_type["id"]]["record"]["parameters"] : []);
                            }
                        }

                        if ($concept_type["sign"] == '+') {
                            $this->assignations_total +=
                                $nameDecimalFunction($concept_type["value"], $this->number_decimals->p_value);
                        } elseif ($concept_type["sign"] == '-') {
                            $this->deductions_total +=
                                $nameDecimalFunction($concept_type["value"], $this->number_decimals->p_value);
                        }
                        $this->current_total +=
                            $nameDecimalFunction($concept_type["value"], $this->number_decimals->p_value);
                        $this->total =
                            $nameDecimalFunction(
                                $this->assignations_total - $this->deductions_total,
                                $this->number_decimals->p_value
                            );
                    } else {
                        unset($concept_type_arr[$index]);
                    }
                }
            }

            $pdf = new ReportRepository();

            $pdf->setConfig([
                'institution' => $institution,
                'orientation' => 'L',
                'format' =>
                'A2 LANDSCAPE',
                'filename' => sys_get_temp_dir() . '/' . $payrollStaff['payroll_staff']['id_number'] . '.pdf',
                'urlVerify' => url('/login')
            ]);

            $pdf->setHeader(
                'Recibo de pago',
                'Periodo ' . date('d-m-Y', strtotime($startDate)) . ' / ' . date(
                    'd-m-Y',
                    strtotime($endDate)
                ),
                true,
                false,
                '',
                'C',
                'C'
            );

            $pdf->setFooter();

            $pdf->setBody('payroll::pdf.receipt', true, [
                'pdf' => $pdf,
                'institution' => $institution,
                'currency' => $currency,
                'data' => $payrollStaff,
                'has_params' => $this->has_params,
                'function' => $nameDecimalFunction,
                'decimals' => $this->number_decimals->p_value,
                'month' => $this->month,
                'year' => $this->year,
                'total_' => $this->total,
                'period' => date('d-m-Y', strtotime($startDate)) . ' / ' . date('d-m-Y', strtotime($endDate))
            ], 'F');

            $pdfPath = sys_get_temp_dir() . '/' . $payrollStaff['payroll_staff']['id_number'] . '.pdf';

            $email =
                $payrollStaff['payroll_staff']['payroll_employment']['institution_email'] ??
                $payrollStaff['payroll_staff']['email'];

            PayrollSendReceiptsEmailJob::dispatch(
                $email,
                new SendReceipts(
                    $pdfPath,
                    $this->month,
                    $this->year,
                    date('d-m-Y', strtotime($startDate)) . ' / ' . date('d-m-Y', strtotime($endDate))
                ),
                $pdfPath
            );
        }
    }
}
