<?php

namespace Modules\Payroll\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * @class PayrollVacationsRequestMail
 * @brief Notificación por correo de solicitudes de vacaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationsRequestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @param string $pdfPath Ruta del archivo PDF
     * @param string $subjectMsg Asunto del correo
     *
     * @return void
     */
    public function __construct(
        protected string $pdfPath,
        protected string $subjectMsg
    ) {
    }

    /**
     * Construye el mensaje.
     *
     * @return PayrollVacationsRequestMail
     */
    public function build()
    {
        $path = Storage::path($this->pdfPath);

        $explodedPath = explode('/', $this->pdfPath);

        $fileName = $explodedPath[count($explodedPath) - 1];

        return $this->from(config('mail.from.address'))->subject($this->subjectMsg)->markdown('emails.email', [
            'fromEmail' => config('mail.from.address'),
            'subjectMsg' => $this->subjectMsg,
            'messageText' => 'Adjuntamos las solicitudes de vacaciones',
        ])->attach($path, [
            'as' => $fileName,
            'mime' => 'application/xlsx'
        ]);
    }
}
