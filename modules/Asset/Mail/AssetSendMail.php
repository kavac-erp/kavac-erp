<?php

namespace Modules\Asset\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @class AssetSendMail
 * @brief Gestiona las notificaciones del módulo de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Ruta del archivo
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
     * Correo del remitente
     *
     * @var string $fromEmail
     */
    public $fromEmail;

    /**
     * Mensaje a enviar
     *
     * @var string $messageText
     */
    public $messageText;

    /**
     * Crea una nueva instancia de la clase.
     *
     * @param string $pdfPath Rutal del archivo
     * @param string $subjectMsg Asunto del correo
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
                'as' => 'reporte_de_bienes.pdf',
                'mime' => 'application/pdf'
            ]);
    }
}
