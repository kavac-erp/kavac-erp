<?php

namespace Modules\Payroll\Jobs;

use App\Mail\SystemMail;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Bus\Queueable;
use App\Repositories\ReportRepository;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Modules\Payroll\Actions\GetPayrollArcAction;
use ZipArchive;

/**
 * @class PayrollSendArcJob
 * @brief Trabajo que se encarga de enviar el archivo ARC
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSendArcJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Tiempo de espera para la ejecución del trabajo.
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Método constructor de la clase
     *
     * @param array $data Arreglo con los datos a enviar
     * @param boolean $withZip Indicador de si se envia el archivo como .zip o no
     * @param integer|null $userId Identificador del usuario
     */
    public function __construct(
        protected array $data,
        protected bool $withZip = false,
        protected ?int $userId = null,
    ) {
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        $pathFiles = [];
        $getPayrollArcAction = new GetPayrollArcAction();
        $dataRecords = $getPayrollArcAction->getFormated($this->data, false);

        foreach ($dataRecords as $dataRecord) {
            $fullName = $dataRecord['payroll_staff']['name'];
            $institution = $dataRecord['institution'];
            $fileName = $fullName . '-ARC-' . $dataRecord['year'] . '.pdf';
            $systemName = sys_get_temp_dir() . '/' . $fileName;

            $pdf = new ReportRepository();
            $pdf->setConfig([
                'institution' => $institution,
                'filename' => $systemName,
                'titleIsHTML' => true,
                'reportDate' => ''
            ]);
            $pdf->setHeader(
                '<span style="text-transform: uppercase; font-size: smaller">Comprobante de retención de impuesto sobre la renta anual o de cese de actividades para personas residentes perceptoras de sueldos, salarios y demás remuneraciones similares (ARC)</span>'
            );
            $pdf->setBody('payroll::pdf.payroll-arc', true, [
                'pdf' => $pdf,
                'record' => $dataRecord
            ], 'F');
            array_push($pathFiles, $systemName);

            if (!$this->withZip) {
                $attachmentFiles = [
                    [
                        'path' => $systemName,
                        'name' => $fileName,
                        'mime' => 'application/pdf'
                    ]
                ];

                Mail::to($dataRecord['payroll_staff']['email'])->send(
                    new SystemMail(
                        'Planilla ARC ' . $dataRecord['year'],
                        '',
                        null,
                        $attachmentFiles
                    )
                );
                if (file_exists($systemName)) {
                    unlink($systemName);
                }
            }
        }

        if ($this->withZip) {
            $zip = new ZipArchive();
            $zipName = 'Planillas-ARC-' . $this->data['fiscal_year'] . '.zip';
            $zipPath = sys_get_temp_dir() . '/' . uniqid() . '.zip';

            if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                foreach ($pathFiles as $pathFile) {
                    if (file_exists($pathFile)) {
                        $relativeName = basename($pathFile);
                        $zip->addFile($pathFile, $relativeName);
                    } else {
                        throw new Exception('File does not exist: ' . $pathFile);
                    }
                }

                if ($zip->close()) {
                    foreach ($pathFiles as $pathFile) {
                        if (file_exists($pathFile)) {
                            unlink($pathFile);
                        }
                    }

                    $attachmentFiles = [
                        [
                            'path' => $zipPath,
                            'name' => $zipName,
                            'mime' => 'application/zip'
                        ]
                    ];


                    $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
                    Mail::to($user->email)->send(
                        new SystemMail(
                            'Planillas ARC ' . $this->data['fiscal_year'],
                            '',
                            null,
                            $attachmentFiles
                        )
                    );
                    if (file_exists($zipPath)) {
                        unlink($zipPath);
                    }
                } else {
                    $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
                    $user->notify(new SystemNotification('Alerta', 'Se generó un error al crear el archivo zip. Intente de nuevo más tarde.'));
                }
            } else {
                $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
                $user->notify(new SystemNotification('Alerta', 'Se generó un error al crear el archivo zip. Intente de nuevo mas tarde.'));
            }
        }
    }
}
