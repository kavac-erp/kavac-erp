<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @class SendReceipts
 * @brief Gestiona las notificaciones de recibos de pago
 *
 * Gestiona las notificaciones de recibos de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SendReceipts extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Ruta del archivo
     *
     * @var string $filePath
     */
    private $pdfPath;

    /**
     * Mes del recibo de pago
     *
     * @var string $month
     */
    private $month;

    /**
     * Período del recibo de pago
     *
     * @var string $period
     */
    private $period;

    /**
     * Año del recibo de pago
     *
     * @var string $year
     */
    private $year;

    /**
     * Crea una nueva instancia del mensaje
     *
     * @param string $pdfPath Ruta del archivo
     * @param string $month Mes del recibo de pago
     * @param string $year Año del recibo de pago
     * @param string $period Período del recibo de pago
     */
    public function __construct($pdfPath, $month, $year, $period)
    {
        $this->pdfPath = $pdfPath;
        $this->month = $month;
        $this->year = $year;
        $this->period = $period;
    }

    /**
     * Construye el mensaje
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(
            'Recibo de pago / Mes de ' . $this->month . ' del año ' . $this->year
        )->markdown(
            'emails.receipt-notification',
            ['period' => $this->period]
        )->attach($this->pdfPath, [
            'as' => 'recibo_de_pago.pdf',
            'mime' => 'application/pdf'
        ]);
    }
}
