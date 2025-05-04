<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Log;

class FailRegisterImportExport implements FromArray, ShouldAutoSize, WithHeadings
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function headings(): array
    {
        return [
            'Columna',
            'Atributo',
            'Error',
            'Hoja de Calculo',
        ];
    }
    // public function map($errors): array
    // {
    //     return [
    //         $errors->row,
    //         $errors->attribute,
    //         $errors->errors[0],

    //     ];
    // }

    public function array(): array
    {
        Log::info($this->errors);


        return $this->errors;
    }
}
