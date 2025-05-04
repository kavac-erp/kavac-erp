<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * @class PayrollSendReceiptsJob
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendReceiptsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;

    protected $mailable;

    protected $pdfPath;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @method __construct
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
     * @method handle
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send($this->mailable);

        unlink($this->pdfPath);
    }
}
