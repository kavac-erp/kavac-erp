<?php

namespace Modules\Payroll\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Emails\PayrollStaffPdfReportRequestMail;

/**
 * @class PayrollSendStaffPdfReportEmailJob
 * @brief Trabajo que se encarga de enviar el reporte de los empleados
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendStaffPdfReportEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Tiempo de espera de ejecución del trabajo.
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @param User $user Usuario al cual enviar el correo
     * @param string $pdfPath Ruta del archivo a exportar
     *
     * @return void
     */
    public function __construct(
        protected object $user,
        protected string $pdfPath
    ) {
        //
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        $email = $this->user->email;

        $mailable = new PayrollStaffPdfReportRequestMail($this->pdfPath, 'Reporte de trabajadores');

        Mail::to($email)->send($mailable);

        $pdfPath = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $this->pdfPath;

        Storage::delete($pdfPath);

        $this->user->notify(new SystemNotification('Se ha generado el archivo de reporte de trabajadores', 'Se ha enviado el archivo de reporte de trabajadores a su correo electronico.'));
    }
}
