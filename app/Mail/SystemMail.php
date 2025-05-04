<?php

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
class SystemMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Asunto del mensaje
     *
     * @var string $subjectMsg
     */
    public $subjectMsg;

    /**
     * Cuerpo del mensaje
     *
     * @var string $messageText
     */
    public $messageText;

    /**
     * Email del remitente
     *
     * @var string $fromEmail
     */
    public $fromEmail;
    public $attachmentFiles;

    /**
     * Crea una nueva instancia del mensaje
     *
     * @return void
     */
    public function __construct($subject, $message, $from = null, $attachmentFiles = [])
    {
        $this->subjectMsg = $subject;
        $this->messageText = $message;
        $this->fromEmail = $from ?? config('mail.from.address');
        $this->attachmentFiles = $attachmentFiles;
    }

    /**
     * Construye el mensaje
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from($this->fromEmail)->subject($this->subjectMsg)->markdown('emails.email');

        foreach ($this->attachmentFiles as $file) {
            $email->attach($file['path'], [
                'as' => $file['name'],
                'mime' => $file['mime'],
            ]);
        }

        return $email;
    }
}
