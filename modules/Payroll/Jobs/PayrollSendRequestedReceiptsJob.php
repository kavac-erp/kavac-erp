<?php

/** Trabajos en segundo plano del módulo de Talento Humano */

namespace Modules\Payroll\Jobs;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Parameter;
use App\Models\Institution;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Repositories\ReportRepository;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Emails\SendRequestedReceipts;
use Modules\Payroll\Actions\GetPayrollConceptParameters;
use Modules\Payroll\Actions\PayrollPaymentRelationshipAction;

/**
 * @class PayrollSendRequestedReceiptsJob
 * @brief Instrucciones para enviar recibos de pago de nómina a través de una tarea en segundo plano
 *
 * Enviar recibos de pago de nómina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg at gmail dot com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendRequestedReceiptsJob implements ShouldQueue
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
     * Mes de la nómina
     *
     * @var string $month
     */
    protected $month;

    /**
     * Año de la nómina
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
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(
        protected $payrollStaffPayroll,
        protected Institution $institution,
        protected Currency $currency,
        protected string $email,
        protected ?Parameter $number_decimals = null,
        protected ?Parameter $round = null,
    ) {
        $this->number_decimals = Parameter::query()->where([
            'p_key' => 'number_decimals',
            'required_by' => 'payroll',
        ])->first();

        $this->round = Parameter::query()->where([
            'p_key' => 'round',
            'required_by' => 'payroll',
        ])->first();
        $this->payrollStaffPayroll = $this->payrollStaffPayroll->with([
            "payroll" => function ($query) {
                $query->with([
                    "payrollPaymentPeriod" => function ($query) {
                        $query->select('id', 'start_date', 'end_date');
                    }
                ]);
            }
        ])->with([
            "payrollStaff" => function ($query) {
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
            }
        ])->get()->toArray();
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        $nameDecimalFunction = $this->round->p_value == 'false' ? 'currency_format' : 'round';
        $payrollPaymentRelationshipAction = new PayrollPaymentRelationshipAction();

        $startDate = $this->payrollStaffPayroll[0]['payroll']['payroll_payment_period']['start_date'];
        $endDate = $this->payrollStaffPayroll[0]['payroll']['payroll_payment_period']['end_date'];
        $this->month = Carbon::createFromFormat('Y-m-d', $startDate)->format('F');
        $this->month = months_dictionary()[$this->month];
        $this->year = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y');

        $params = (new GetPayrollConceptParameters());
        $payrollPersonalConceptAssign = [];
        $filesToZip = [];
        $zipPath = '';

        foreach ($this->payrollStaffPayroll as &$payrollStaff) {
            $timeParameters = [];
            array_map(function ($payrollParameter) use (&$timeParameters) {
                $timeParameters[$payrollParameter['staff_id']][] = $payrollParameter;
            }, $payrollPaymentRelationshipAction->getPayrollParameters($payrollStaff['payroll_id'], false));
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
            $staffIdNumber = $payrollStaff['payroll_staff']['id_number'];
            $staffFullName = $payrollStaff['payroll_staff']['first_name'] . ' ' . $payrollStaff['payroll_staff']['last_name'];
            $pdfPath = sys_get_temp_dir() . "/{$staffIdNumber}_" . Str::slug($staffFullName, '_') . ".pdf";

            $pdf->setConfig([
                'institution' => $this->institution,
                'orientation' => 'L',
                'format' =>
                'A2 LANDSCAPE',
                'filename' => $pdfPath,
                'urlVerify' => url('/')
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
                'institution' => $this->institution,
                'currency' => $this->currency,
                'data' => $payrollStaff,
                'has_params' => $this->has_params,
                'function' => $nameDecimalFunction,
                'decimals' => $this->number_decimals->p_value,
                'month' => $this->month,
                'year' => $this->year,
                'total_' => $this->total,
                'period' => date('d-m-Y', strtotime($startDate)) . ' / ' . date('d-m-Y', strtotime($endDate))
            ], 'F');

            $filesToZip[] = $pdfPath;
        }

        $zip = new ZipArchive();
        $zipName = 'recibos_de_pago.zip';
        $zipPath = Storage::disk('temporary')->path($zipName);
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($filesToZip as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        PayrollSendReceiptsEmailJob::dispatch(
            $this->email,
            new SendRequestedReceipts(
                $zipPath,
                $this->month,
                $this->year,
                date('d-m-Y', strtotime($startDate)) . ' / ' . date('d-m-Y', strtotime($endDate))
            ),
            $zipPath
        );
    }
}
