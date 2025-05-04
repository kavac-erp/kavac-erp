<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @class FailMailableClass
 * @brief Gestiona las notificaciones de procesos fallidos al importar datos
 *
 * Gestiona las notificaciones de procesos fallidos al importar datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FailMailableClass extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Ruta del archivo adjunto
     *
     * @var string $filePath
     */
    protected $filePath;

    /**
     * Crea una nueva instancia de la notificación
     *
     * @return void
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Construye el mensaje
     *
     * @return $this
     */
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
