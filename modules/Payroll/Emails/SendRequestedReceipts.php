<?php

namespace Modules\Payroll\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @class SendRequestedReceipts
 * @brief Notificación por correo de recibos de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SendRequestedReceipts extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @param string $filePath Ruta del archivo PDF
     * @param string $month Mes del reporte
     * @param string $year Año del reporte
     * @param string $period Periodo solicitado
     *
     * @return void
     */
    public function __construct(
        private string $filePath,
        private string $month,
        private string $year,
        private string $period
    ) {
        //
    }

    /**
     * Construye el mensaje.
     *
     * @return SendRequestedReceipts
     */
    public function build()
    {
        return $this->subject(
            "Recibos de pago / Mes de {$this->month} del año {$this->year}"
        )->markdown(
            'emails.receipt-notification',
            ['period' => $this->period]
        )->attach($this->filePath, [
            'as' => 'recibos_de_pago.zip',
            'mime' => 'application/zip'
        ]);
    }
}
