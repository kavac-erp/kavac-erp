<?php

namespace Modules\Payroll\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * @class PayrollPdfExport
 * @brief Clase que exporta el listado de registros de nómina en formato pdf
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPdfExport implements FromView
{
    /**
     * Datos a exportar
     *
     * @var array $data
     */
    protected $data;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct(array $view_data)
    {
        $this->data = $view_data;
    }

    /**
     * Retorna la vista a exportar
     *
     * @return \Illuminate\View\View
     */
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
