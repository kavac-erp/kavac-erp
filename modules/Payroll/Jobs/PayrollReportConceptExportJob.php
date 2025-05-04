<?php

namespace Modules\Payroll\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SystemNotification;
use Modules\Payroll\Mail\PayrollSendMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Exports\PayrollReportConceptExport;

/**
 * @class PayrollReportConceptExportJob
 * @brief Trabajo que se encarga de exportar los conceptos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollReportConceptExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use SerializesModels;

    /**
     * Arreglo con los datos de la petición
     *
     * @var array $requestArray
     */
    protected $requestArray;

    /**
     * Usuario que realiza la petición
     *
     * @var User $user
     */
    protected $user;

    /**
     * Fecha de creación del reporte
     *
     * @var string $created_at
     */
    protected $created_at;

    /**
     * Ruta del archivo a exportar
     *
     * @var string $pdfPath
     */
    protected $pdfPath;

    /**
     * Nombre del archivo
     *
     * @var string $filename
     */
    protected $filename;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct($requestArray)
    {
        $this->requestArray = $requestArray;
        $this->user = auth()->user();
        $this->created_at = \Carbon\Carbon::now();
        $this->filename = 'report_concept' . $this->created_at . '.xlsx';
        $this->pdfPath = storage_path() . '/app/temporary/' . $this->filename;
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        (new PayrollReportConceptExport($this->requestArray))->store('temporary/report_concept' . $this->created_at . '.xlsx');

        if ($this->user) {
            $this->user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha generado el reporte de conceptos, '
                    . 'el archivo ha sido enviado a su correo electrónico',
                )
            );

            $email = $this->user->email;
        }

        $mailable = new PayrollSendMail($this->pdfPath, 'Se ha generado el reporte de conceptos');

        Mail::to($email)->send($mailable);

        Storage::delete($this->pdfPath);
    }
}
