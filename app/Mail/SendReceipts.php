<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReceipts extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $pdfPath;

    private $month;

    private $period;

    private $year;

    /**
     * Create a new message instance.
     *
     * @param string $pdfPath
     */
    public function __construct($pdfPath, $month, $year, $period)
    {
        $this->pdfPath = $pdfPath;
        $this->month = $month;
        $this->year = $year;
        $this->period = $period;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Recibo de pago / Mes de ' . $this->month . ' del año ' . $this->year)->markdown('emails.receipt-notification', ['period' => $this->period])
            ->attach($this->pdfPath, [
                'as' => 'recibo_de_pago.pdf',
                'mime' => 'application/pdf'
            ]);
    }
}
