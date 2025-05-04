<?php

namespace Modules\Asset\Imports;

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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Modules\Payroll\Exports\FailRegisterImportExport;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @class AssetExport
 * @brief Gestiona la importación de datos de bienes en múltiples hojas
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetImportMultiSheet implements
    WithMultipleSheets,
    WithChunkReading,
    ShouldQueue,
    WithEvents
{
    use Importable;
    use SkipsFailures;

    /**
     * Método constructor de la clase
     *
     * @param string $type Tipo de importación
     * @param string $filePath Ruta del archivo
     * @param string $disk Sistema de disco a usar
     * @param integer $userId ID del usuario
     * @param string $fileErrosPath Ruta del archivo de errores
     * @param array $sheets Array de hojas
     *
     * @throws \Exception
     */
    public function __construct(
        protected string $type,
        protected string $filePath,
        protected string $disk,
        protected int $userId,
        protected string $fileErrosPath,
        protected array $sheets = [],
    ) {
        $spreadsheet = IOFactory::load(storage_path($disk . '/' . $filePath));

        foreach ($spreadsheet->getSheetNames() ?? [] as $sheetName) {
            $match = match ($sheetName) {
                'Registros de mueble' => new AssetImport($type, $fileErrosPath),
                'Registros de inmueble' => new AssetImport($type, $fileErrosPath),
                'Registros de vehiculo' => new AssetImport($type, $fileErrosPath),
                'Registros de semoviente' => new AssetImport($type, $fileErrosPath),
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

    /**
     * Tamaño de los registros por hoja
     *
     * @return integer
     */
    public function chunkSize(): int
    {
        return 50;
    }

    /**
     * Define las hojas a importar
     *
     * @return array<mixed>
     */
    public function sheets(): array
    {
        return $this->sheets;
    }

    /**
     * Registro de eventos
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();
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
                        array_push($errors, json_decode($line, true));
                    }
                }
                $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();

                if (count($errors) > 0) {
                    $importNotificationMessage = 'Alguno de los registros que trataste de importar fallaron.';
                    $sendEmailMessage = '';

                    $errorExcelFiles = [
                        [
                            'file' => Excel::raw(new FailRegisterImportExport($errors), \Maatwebsite\Excel\Excel::XLSX),
                            'fileName' => 'Errores_de_importacion.xlsx',
                        ]
                    ];

                    if ($user->email) {
                        try {
                            Mail::to($user->email)->send(new FailImportNotification($errorExcelFiles));
                        } catch (\Exception $e) {
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
