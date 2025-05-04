<?php

namespace Modules\Budget\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @class BudgetSendMail
 * @brief Clase que gestiona las notificaciones por correo del módulo de presupuesto
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Ruta del archivo a enviar por correo
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
     * Datos del remitente
     *
     * @var string $fromEmail
     */
    public $fromEmail;

    /**
     * Mensaje del correo
     *
     * @var string $messageText
     */
    public $messageText;

    /**
     * Crea una nueva instancia de la clase.
     *
     * @param string $pdfPath Ruta del archivo
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
     * Gestiona el envío del mensaje
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'))->subject($this->subjectMsg)->markdown('emails.email')
            ->attach($this->pdfPath, [
                'as' => 'reporte_de_presupuesto.pdf',
                'mime' => 'application/pdf'
            ]);
    }
}
