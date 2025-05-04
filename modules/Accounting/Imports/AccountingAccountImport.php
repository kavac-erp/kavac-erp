<?php

namespace Modules\Accounting\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Accounting\Jobs\AccountingManageImport;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\User;
use App\Mail\FailImportNotification;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\FailRegisterImportExport;

/**
 * @class AccountingAccountImport
 * @brief Gestiona la importación de datos del reporte de estado de resultados
 *
 * Realiza el proceso de importación de datos del reporte de estado de resultados
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingAccountImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    WithValidation,
    SkipsOnFailure
{
    use Importable;

    /**
     * Líneas que generan errores al momento de importar
     *
     * @var array $lines
     */
    protected $lines;

    /**
     * @param array $row Datos de la fila a importar
     *
     * @return void
     */
    public function model(array $row)
    {
        AccountingManageImport::dispatch($row);
    }

    /**
     * Reglas de validación
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return     array           Devuelve un arreglo con las reglas de validación
     */
    public function rules(): array
    {
        return [
            '*.codigo' => ['required'],
            '*.denominacion' => ['required'],
            '*.activa' => ['required', 'max:2'],
            '*.original' => ['required', 'max:2'],
        ];
    }

    /**
     * Mensajes de validación personalizados
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return     array                      Devuelve un arreglo con los mensajes de validación personalizados
     */
    public function customValidationMessages(): array
    {
        return [
            'codigo.required' => 'Error en la fila :row. El campo :attribute es obligatorio',
            'denominacion.required' => 'Error en la fila :row. El campo :attribute es obligatorio',
            'activa.required' => 'Error en la fila :row. El campo :attribute es obligatorio',
            'activa.max' => 'Error en la fila :row. El campo :attribute debe ser de maximo 2 caracteres.',
            'original.required' => 'Error en la fila :row. El campo :attribute es obligatorio',
            'original.max' => 'Error en la fila :row. El campo :attribute debe ser de maximo 2 caracteres.',
        ];
    }

    /**
     * Establece el tamaño máximo a importar
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 200;
    }

    /**
     * Establece el tamaño de cada trozo a importar
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * Gestiona los errores del archivo al importar datos
     *
     * @param \Maatwebsite\Excel\Validators\Failure[] $failures
     *
     * @return void
     */
    public function onFailure(Failure ...$failures)
    {
        // Handle the failures how you'd like.
    }

    /**
     * Realiza las acciones necesarias después de importar datos
     *
     * @param \Maatwebsite\Excel\Events\AfterImport $event Evento de importación
     *
     * @return void
     */
    public static function afterImport(AfterImport $event)
    {
        $email = auth()->user()->email;

        $messageStatus = 'Éxito';
        $messageContent = 'Importación exitosa.';

        if (!empty(self::$errors)) {
            $messageStatus = 'Error';
            $messageContent = 'Carga masiva de cuentas: Fallos de Importacion de registros.';

            $errorExcelFiles = [
                [
                    'file' => Excel::raw(new FailRegisterImportExport(self::$lines), \Maatwebsite\Excel\Excel::XLSX),
                    'fileName' => 'Errores_de_importacion_contabilidad.xlsx',
                ]
            ];
        }

        $user = User::find(auth()->user()->id);

        if (!empty(self::$errors)) {
            try {
                Mail::to($email)->send(new FailImportNotification($errorExcelFiles));
            } catch (\Exception $e) {
                Log::info($e);
                $sendEmailMessage = 'No se pudo enviar el correo de importación. ';
                $user->notify(new SystemNotification('Error', $sendEmailMessage));
            }
        }

        $user->notify(new SystemNotification($messageStatus, $messageContent));
    }
}
