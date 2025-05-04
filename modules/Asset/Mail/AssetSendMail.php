<?php

namespace Modules\Asset\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssetSendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $pdfPath;
    public $subjectMsg;
    public $fromEmail;
    public $messageText;

    /**
     * Create a new message instance.
     *
     * @param string $pdfPath
     */
    public function __construct($pdfPath, $subjectMsg)
    {
        $this->pdfPath = $pdfPath;
        $this->subjectMsg = $subjectMsg;
        $this->messageText = '';
        $this->fromEmail = config('mail.from.address');
    }

    /**
     * Build the message.
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
