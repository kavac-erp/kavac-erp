<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @class FailImportNotification
 * @brief Gestiona las notificaciones de procesos de importación de datos fallido
 *
 * Gestiona las notificaciones de procesos de importación de datos fallido
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FailImportNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Arreglo con los archivos adjuntos
     *
     * @var array $attachentsFiles
     */
    public array $attachentsFiles;


    /**
     * Crea una nueva instancia de la notificación
     *
     * @return void
     */
    public function __construct(array $attachents = [],)
    {
        $this->attachentsFiles = $attachents;
    }

    /**
     * Construye el mensaje
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject(
            config('app.name') . " - Fallos de Importacion de registros"
        )->markdown('emails.fail-register-import');

        foreach ($this->attachentsFiles as $index => $file) {
            $email->attachData($file['file'], $file['fileName'] ?? $index . '.xlsx');
        }

        return $email;
    }
}
