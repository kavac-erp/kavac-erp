<?php

namespace Modules\Payroll\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialStaffExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function headings(): array
    {
        return [
            'cedula_de_identidad',
            'banco',
            'tipo_de_cuenta',
            'numero_de_cuenta',
            'tipo_de_cuenta_value',
            'banco_value',
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

    public function title(): string
    {
        return 'Datos Financieros';
    }

    public function array(): array
    {

        // 'Columna' => $failure->row(),
        //                 'Atributo' => $failure->attribute(),
        //                'Error' => $var[0],
        // return [
        //     [$this->errors["Columna"], $this->errors["Atributo"],  2],
        //     [4, 5, 6],
        // ];

        return $this->errors;
    }
}
