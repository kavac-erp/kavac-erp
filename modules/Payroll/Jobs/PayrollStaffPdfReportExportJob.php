<?php

namespace Modules\Payroll\Jobs;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * @class PayrollStaffPdfReportExportJob
 * @brief Trabajo que se encarga de generar el pdf del reporte de personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffPdfReportExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Objeto de la institución
     *
     * @var Institution $institution
     */
    protected Institution $institution;

    /**
     * Nombre del archivo
     *
     * @var string $filename
     */
    protected string $filename;

    /**
     * Cuerpo del pdf
     *
     * @var string $pdfBody
     */
    protected string $pdfBody;

    /**
     * Objeto para generar el pdf
     *
     * @var object $pdf
     */
    protected object $pdf;

    /**
     * Array de registros para mostrar en el reporte
     *
     * @var array $records
     */
    protected array $records;

    /**
     * Array de columnas para el reporte
     *
     * @var array $columns
     */
    protected array $columns;

    /**
     * Array de datos del request
     *
     * @var array $request
     */
    protected array $request;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        [
            $this->institution,
            $this->filename,
            $this->pdfBody,
            $this->pdf,
            $this->columns,
            $this->request
        ]
            = $data;
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle(): void
    {
        $payrollStaff   = new PayrollStaff();
        $records        = $payrollStaff->filterPayrollStaff($this->request)->get()->toArray();

        $this->pdf->setConfig(
            [
                'institution'   => $this->institution,
                'urlVerify'     => url(''),
                'orientation'   => count($this->columns) > 6 ? 'L' : 'P',
                'filename'      => $this->filename,
            ]
        );

        $this->pdf->setHeader("Reporte de Trabajadores");

        $this->pdf->setFooter(true, strip_tags($this->institution->legal_address));

        $this->pdf->setBody(
            $this->pdfBody,
            true,
            [
                'pdf'       => $this->pdf,
                'records'   => $records,
                'columns'   => $this->columns
            ]
        );
    }
}
