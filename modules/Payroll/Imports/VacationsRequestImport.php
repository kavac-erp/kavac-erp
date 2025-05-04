<?php

namespace Modules\Payroll\Imports;

use App\Models\CodeSetting;
use App\Notifications\SystemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollVacationRequest;
use Modules\Payroll\Rules\DaysRequested;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * @class VacationsRequestImport
 * @brief Importa un archivo de solicitudes de vacaciones
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class VacationsRequestImport implements
    ToModel,
    WithValidation,
    SkipsEmptyRows,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    SkipsOnFailure,
    ShouldQueue
{
    /**
     * Método constructor de la clase
     *
     * @param string $errorsFilePath Ruta del archivo de errores
     * @param object $currentFiscalYear Año fiscal actual
     * @param object $user Usuario
     * @param integer $institutionId Identificador de la institución
     *
     * @return void
     */
    public function __construct(
        protected string $errorsFilePath,
        protected object $currentFiscalYear,
        protected object $user,
        protected int $institutionId
    ) {
        //
    }

    /**
     * Tamaño de los trozos de datos
     *
     * @return integer
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Tamaño del lote de datos
     *
     * @return integer
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Fila del encabezado
     *
     * @return integer
     */
    public function headingRow(): int
    {
        return 1;
    }

    /**
     * Modelo para importar datos
     *
     * @param array $row Arreglo con los datos a importar
     *
     * @return PayrollVacationRequest
     */
    public function model(array $row)
    {
        $codeSetting = CodeSetting::where('table', 'payroll_vacation_requests')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($this->currentFiscalYear) ?
                substr($this->currentFiscalYear->year, 2, 2) : date('y')) : (isset($this->currentFiscalYear) ?
                $this->currentFiscalYear->year : date('Y')),
            PayrollVacationRequest::class,
            $codeSetting->field
        );

        return new PayrollVacationRequest([
            'code'                 => $code,
            'status'               => 'approved',
            'days_requested'       => $row['days_requested'],
            'vacation_period_year' => $row['vacation_period_year'],
            'start_date'           => $row['start_date'],
            'end_date'             => $row['end_date'],
            'payroll_staff_id'     => $row['payroll_staff_id'],
            'institution_id'       => $this->institutionId
        ]);
    }

    /**
     * Preparar los datos para ser importados (validaciones)
     *
     * @param array $data Arreglo con los datos
     * @param integer $index Indice de la fila
     *
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        /* Fila del excel bajo procesamiento */
        $row = [];

        try {
            /* Hallar el id del trabajador */
            $row['payroll_staff_id'] = PayrollStaff::query()
                ->where('id_number', $data['cedula_del_trabajador'])
                ->toBase()->first()->id;

            /* Encuentra los años para los periodos solicitados y los codifica en json */
            $row['vacation_period_year'] = json_encode(
                collect(
                    explode(',', $data['anos_del_periodo_vacacional'])
                )->map(function ($year) {
                    return [
                        "id" => $year,
                        "text" => $year,
                        "yearId" => '',
                    ];
                })
            );
            /* Cantidad de dias solicitados */
            $row['days_requested'] = $data['dias_solicitados'];

            /* Fecha de inicio de vacaciones */
            $row['start_date'] = Date::excelToDateTimeObject($data['fecha_de_inicio_de_vacaciones']);

            /* Fecha de culminación de vacaciones */
            $row['end_date'] = Date::excelToDateTimeObject($data['fecha_de_culminacion_de_vacaciones']);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $data;
        }

        return $row;
    }

    /**
     * Reglas de validación
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payroll_staff_id'      =>  ['required', 'exists:payroll_staffs,id'],
            'vacation_period_year'  =>  ['required'],
            'days_requested'        =>  ['required', new DaysRequested()],
            'end_date'              =>  ['required', 'date', 'after:*.start_date'],
            'start_date'            =>  ['required', 'date', 'before:*.end_date'],
        ];
    }

    /**
     * Mensajes personalizados de validación
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'payroll_staff_id.required'     => 'La cédula del trabajador es obligatorio.',
            'payroll_staff_id.exists'       => 'La cédula del trabajador no existe.',
            'vacation_period_year.required' => 'Los años del periodo vacacional son obligatorios.',
            'days_requested.required'       => 'Los dias solicitados son obligatorios.',
            'end_date.after'                => 'La fecha de culminación del periodo vacacional debe ser mayor a la fecha de inicio del mismo.',
            'start_date.before'             => 'La fecha de inicio del periodo vacacional debe ser menor a la fecha de culminación del mismo.',
        ];
    }

    /**
     * Callback de error de validación
     *
     * @param Failure[] $failures Arreglo columnas que fallaron en la validación
     */
    public function onFailure(Failure ...$failures)
    {
        $failuresCollection = collect($failures);

        $row = $failuresCollection->first()->row();

        $message = "Errores en la fila N°" . strval($row) . ":" . "\n";

        $failuresCollection->each(function ($failure) use (&$message) {
            $message .= "--> " . $failure->errors()[0] . "\n";
        });

        $message .= "\n";

        Storage::disk('temporary')->append($this->errorsFilePath, $message);
    }

    /**
     * Registros de eventos de importación
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                $exception = $event->getException();
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
                    $this->user->notify(new SystemNotification('Error', 'Importación fallida. ' . ucfirst($errorMessage) . ' ' . $bindingsString));
                } else {
                    $this->user->notify(new SystemNotification('Error', 'Importación fallida. Para mas información comuniquese con el administrador'));
                }
            },
        ];
    }
}
