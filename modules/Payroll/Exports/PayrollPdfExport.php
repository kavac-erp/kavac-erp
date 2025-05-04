<?php

namespace Modules\Payroll\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PayrollPdfExport implements FromView
{
    protected $data;

    /**
     * Write code on Method
     * @return response()
     */

    public function __construct(array $view_data)
    {
        $this->data = $view_data;
    }

    public function view(): View
    {
        return view('payroll::pdf.payroll-budget-accounting-report', [
            // 'records' => $this->data['records'],
            // 'institution' => $this->data['institution'],
            // 'currencySymbol' => $this->data['currencySymbol'],
            // 'fiscal_year' => $this->data['fiscal_year'],
            // 'report_date' => $this->data['report_date'],
            // 'initialDate' => $this->data['initialDate'],
            // 'finalDate' => $this->data['finalDate'],
        ]);
    }
}
