<?php

namespace Modules\Payroll\Exports;

use App\Models\Institution;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * @class PayrollReceiptExport
 * @brief Clase que exporta el recibo de pago de la nómina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollReceiptExport implements FromView
{
    /**
     * Datos a exportar
     * @var array $data
     */
    protected $data;

    /**
     * Datos de la institución
     *
     * @var Institution $institution
     */
    protected $institution;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct($data, $institution)
    {
        $this->data = $data;
        $this->institution = $institution;
    }

    /**
     * Retorna la vista a exportar
     *
     * @return \Illuminate\View\View
     */
    public function view(): View
    {
        return view('payroll::pdf.receipt', ['data' => $this->data, 'institution' => $this->institution]);
    }
}
