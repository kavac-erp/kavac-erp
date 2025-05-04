<?php

namespace Modules\Payroll\Imports\Staff;

use App\Models\User;
use App\Mail\FailImportNotification;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Modules\Payroll\Imports\EmploymentStaffImport;
use Modules\Payroll\Imports\FinancialStaffImport;
use Modules\Payroll\Imports\ProfessionalStaffImport;
use Modules\Payroll\Imports\SocioStaffImport;
use Modules\Payroll\Imports\StaffImport;
use Modules\Payroll\Imports\StaffAccountImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Modules\Payroll\Exports\FailRegisterImportExport;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class RegisterStaffImport implements
    WithMultipleSheets,
    WithChunkReading,
    ShouldQueue,
    WithEvents
{
    use Importable;
    use SkipsFailures;

    public function __construct(
        protected string $filePath,
        protected string $disk,
        protected object $user,
        protected string $fileErrosPath,
        protected array $sheets = [],
    ) {
        $spreadsheet = IOFactory::load(storage_path($disk . '/' . $filePath));

        foreach ($spreadsheet->getSheetNames() ?? [] as $sheetName) {
            $match = match ($sheetName) {
                'Datos Personales' => new StaffImport($fileErrosPath),
                'Datos Profesionales' => new ProfessionalStaffImport($fileErrosPath),
                'Datos Socioeconomicos' => new SocioStaffImport($fileErrosPath),
                'Datos Laborales' => new EmploymentStaffImport($fileErrosPath),
                'Datos Financieros' => new FinancialStaffImport($fileErrosPath),
                'Datos Contables' => new StaffAccountImport($fileErrosPath),
                default => null
            };
            if ($match) {
                $this->sheets[$sheetName] = $match;
            }
        }
        if (empty($this->sheets)) {
            throw new \Exception('No existen hojas de importación validas');
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }

    /** @return array<mixed> */
    public function sheets(): array
    {
        return $this->sheets;
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $user = User::find($this->user->id);
                $exception = $event->getException();
                Storage::disk('temporary')->delete($this->filePath);

                if ($exception instanceof QueryException) {
                    $bindingsString = implode(',', $exception->getBindings() ?? []);
                    $message = str_replace("\n", "", $exception->getMessage());
                    if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                        $pattern = '/ERROR:(.*?)DETAIL/';
                        preg_match($pattern, $message, $matches);
                        $errorMessage = trim($matches[1]);
                    } else {
                        $errorMessage = $message;
                    }
                    $user->notify(new SystemNotification('Error', 'Importación fallida. ' . ucfirst($errorMessage) . ' ' . $bindingsString));
                } else {
                    Log::error($event->getException());
                    $user->notify(new SystemNotification('Error', 'Importación fallida. Para mas información comuniquese con el administrador'));
                }
            },
            AfterImport::class => function (AfterImport $event) {
                Storage::disk('temporary')->delete($this->filePath);
                $fileErros = Storage::disk('temporary')->get($this->fileErrosPath);
                $lines = explode("\n", $fileErros);
                $errors = [];
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        array_push($errors,json_decode($line,true));
                    }
                }
                $user = User::find($this->user->id);

                if (count($errors) > 0) {
                    $importNotificationMessage = 'Alguno de los registros que trataste de importar fallaron.';
                    $sendEmailMessage = '';
                    // $excelFile = Excel::raw(new FailRegisterImportExport($errors), \Maatwebsite\Excel\Excel::XLSX);

                    $errorExcelFiles = [
                        [
                            'file' => Excel::raw(new FailRegisterImportExport($errors), \Maatwebsite\Excel\Excel::XLSX),
                            'fileName' => 'Errores_de_importacion.xlsx',
                        ]
                    ];

                    $email = $this->user->email;

                    if ($email) {
                        try {
                            Mail::to($email)->send(new FailImportNotification($errorExcelFiles));
                        } catch (\Exception$e) {                         
                            Log::info($e);
                            $sendEmailMessage = 'No se pudo enviar el correo de importación. ';
                        }
                    }
                    $user->notify(new SystemNotification('Fallos de Importacion de registros', $importNotificationMessage . ' ' . $sendEmailMessage));
                } else {
                    $user->notify(new SystemNotification('Éxito', 'Importación exitosa.'));
                }
                Storage::disk('temporary')->delete($this->fileErrosPath);
            },
        ];
    }
}
