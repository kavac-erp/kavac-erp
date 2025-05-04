<?php

namespace Modules\Payroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * @class PayrollSendReceiptsEmailJob
 * @brief Trabajo que se encarga de enviar los recibos de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendReceiptsEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Correo electrónico del destinatario
     *
     * @var string $email
     */
    protected $email;

    /**
     * Objeto con la información del correo a envíar
     *
     * @var object $mailable
     */
    protected $mailable;

    /**
     * Ruta del archivo del recibo de pago
     * @var string $pdfPath
     */
    protected $pdfPath;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct($email, $mailable, $pdfPath)
    {
        $this->email = $email;
        $this->mailable = $mailable;
        $this->pdfPath = $pdfPath;
    }
    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            // Verifica si es un correo válido para envíar el mensaje
            Mail::to($this->email)->send($this->mailable);

            unlink($this->pdfPath);
        }
    }
}
