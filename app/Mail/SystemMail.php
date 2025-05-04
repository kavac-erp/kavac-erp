<?php

/** Gestión de correos del sistema */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @class SystemMail
 * @brief Gestiona los correos de notificación de uso general del sistema
 *
 * Gestiona los correos de notificación de uso general del sistema
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SystemMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $subjectMsg;
    public $messageText;
    public $fromEmail;

    /**
     * Crea una nueva instancia del mensaje
     *
     * @return void
     */
    public function __construct($subject, $message, $from = null)
    {
        $this->subjectMsg = $subject;
        $this->messageText = $message;
        $this->fromEmail = $from ?? config('mail.from.address');
    }

    /**
     * Construye el mensaje
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->fromEmail)->subject($this->subjectMsg)->markdown('emails.email');
    }
}
