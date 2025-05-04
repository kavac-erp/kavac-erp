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
use Modules\Payroll\Emails\PayrollVacationsRequestMail;
use Modules\Payroll\Exports\PayrollVacationRequestExport;

/**
 * @class PayrollVacationsExportJob
 * @brief Trabajo que se encarga de exportar las vacaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationsExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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

        $mailable = new PayrollVacationsRequestMail($this->pdfPath, 'Solicitudes de vacaciones');

        Mail::to($email)->send($mailable);

        Storage::delete('tmp/solicitudes-de-vacaciones-' . now() .  '.xlsx');

        $this->user->notify(new SystemNotification('Se ha generado el archivo de solicitudes de vacaciones', 'Se ha enviado el archivo de solicitudes de vacaciones a su correo electronico.'));
    }
}
