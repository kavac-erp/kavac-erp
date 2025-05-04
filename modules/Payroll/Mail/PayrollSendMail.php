<?php

namespace Modules\Payroll\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @class PayrollSendMail
 * @brief Envió de correo de la nómina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Ruta del reporte
     *
     * @var string $pdfPath
     */
    public $pdfPath;

    /**
     * Asunto del mensaje
     *
     * @var string $subjectMsg
     */
    public $subjectMsg;

    /**
     * Correo electrónico del remitente
     *
     * @var string $fromEmail
     */
    public $fromEmail;

    /**
     * Texto del mensaje
     *
     * @var string $messageText
     */
    public $messageText;

    /**
     * Crea una nueva instancia del mensaje
     *
     * @param string $pdfPath Ruta del archivo de la nómina
     * @param string $subjectMsg Asunto del mensaje
     *
     * @return void
     */
    public function __construct($pdfPath, $subjectMsg)
    {
        $this->pdfPath = $pdfPath;
        $this->subjectMsg = $subjectMsg;
        $this->messageText = '';
        $this->fromEmail = config('mail.from.address');
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))->subject($this->subjectMsg)->markdown('emails.email')
            ->attach($this->pdfPath, [
                'as' => 'reporte_de_conceptos.xlsx',
                'mime' => 'application/xlsx'
            ]);
    }
}
