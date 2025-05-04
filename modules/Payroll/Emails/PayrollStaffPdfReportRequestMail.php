<?php

namespace Modules\Payroll\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * @class PayrollStaffPdfReportRequestMail
 * @brief Notificación por correo al personal con el(los) reporte(s) solicitado(s)
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffPdfReportRequestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Crea una nueva instancia del mensaje.
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
     * @return PayrollStaffPdfReportRequestMail
     */
    public function build()
    {
        $path = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $this->pdfPath;

        $explodedPath = explode('/', $this->pdfPath);

        $fileName = $explodedPath[count($explodedPath) - 1];

        return $this->from(config('mail.from.address'))->subject($this->subjectMsg)->markdown('emails.email', [
            'fromEmail' => config('mail.from.address'),
            'subjectMsg' => $this->subjectMsg,
            'messageText' => 'Se ha adjuntado el reporte de trabajadores',
        ])->attach($path, [
            'as' => $fileName,
            'mime' => 'application/pdf'
        ]);
    }
}
