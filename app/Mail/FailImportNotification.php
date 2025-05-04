<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailImportNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

        public array $attachentsFiles;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $attachents = [],)
    {

        $this->attachentsFiles = $attachents;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $email = $this->subject(config('app.name') . " - Fallos de Importacion de registros")->markdown('emails.fail-register-import');

        foreach ($this->attachentsFiles as $index => $file) {
            $email->attachData($file['file'], $file['fileName'] ?? $index . '.xlsx');
        }

        return $email;
    }
}
