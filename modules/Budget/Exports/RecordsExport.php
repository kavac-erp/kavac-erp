<?php

namespace Modules\Budget\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RecordsExport implements FromView
{
    protected $data;

    /**
     * Write code on Method
     * @return response()
     */

    public function __construct(array $view_data)
    {
        // dd($view_data);
        $this->data = $view_data;
    }

    public function view(): View
    {
        return view('budget::pdf.budgetAnalyticMajor', [
            'records' => $this->data['records'],
            'institution' => $this->data['institution'],
            'currencySymbol' => $this->data['currencySymbol'],
            'fiscal_year' => $this->data['fiscal_year'],
            'report_date' => $this->data['report_date'],
            'initialDate' => $this->data['initialDate'],
            'finalDate' => $this->data['finalDate'],
        ]);
    }
}

// class RecordsExport implements FromView

// {

//     protected $data;

//     /**
//      * Write code on Method
//      * @return response()
//      */

//     public function __construct($budgetAccounts)

//     {

//         $this->data = $budgetAccounts;
//     }

//     /**
//      * Write code on Method
//      *
//      * @return response()
//      */

//     public function collection()

//     {
//         return collect($this->data);
//     }

//     /**
//      * Write code on Method
//      *
//      * @return response()
//      */

//     public function headings(): array
//     {
//         return [
//             'Fecha',
//             'Código',
//             'Denominación',
//             'Asignado',
//             'Aumento',
//             'Disminución',
//             'Actual',
//             'Comprometido',
//             'Causado',
//             'Pagado',
//             'Disponible',
//         ];
//     }

//     public function map($budgetAccount): array
//     {
//         return [
//             /* Fecha */
//             date_format($budgetAccount['created_at'], 'd-m-Y') ?? '',

//             /* Código */
//             $budgetAccount['budgetAccount']['code'] ?? '',

//             /* Denominación */
//             $budgetAccount['budgetAccount']['denomination'] ?? '',

//             /* Asignado */
//             $budgetAccount['total_year_amount'] ?? '',

//             /* Aumento */
//             $budgetAccount['increment'] ?? '',

//             /* Disminución */
//             number_format($budgetAccount['decrement'], 2) ?? '',

//             /* Actual */
//             $budgetAccount['current'] ?? '',

//             /* Comprometido */
//             $budgetAccount['compromised'] ?? '',

//             /* Causado */
//             '0',

//             /* Pagado */
//             '0',

//             /* Disponible */
//             $budgetAccount['total_year_amount_m'] ?? ''
//         ];
//     }
// }
