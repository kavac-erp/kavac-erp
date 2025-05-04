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
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        AccountingManageImport::dispatch($row);
    }

    /**
     * Reglas de validación
     *
     * @method     rules
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     *
     * @return     Array           Devuelve un arreglo con las reglas de validación
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
     * @method     customValidationMessages
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
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
    public function batchSize(): int
    {
        return 200;
    }
    public function chunkSize(): int
    {
        return 200;
    }
    public function onFailure(Failure ...$failures)
    {
        // Handle the failures how you'd like.
    }
}
