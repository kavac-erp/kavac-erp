<?php

namespace Modules\Payroll\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PayrollReceiptExport implements FromView
{
    protected $data;
    protected $institution;

    /**
     * Write code on Method
     * @return response()
     */

    public function __construct($data, $institution)
    {
        $this->data = $data;
        $this->institution = $institution;
    }

    /**
     *
     */
    public function view(): View
    {
        return view('payroll::pdf.receipt', ['data' => $this->data, 'institution' => $this->institution]);
    }
}
