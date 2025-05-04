<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailMailableClass extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function build()
    {
        return $this->subject(config('app.name') . " - Fallos de Importacion de registros")
            ->markdown('emails.fail-register-import')
            ->attach(
                $this->filePath,
                [
                    'as' => 'fail_register.xlsx',
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
    }
}
