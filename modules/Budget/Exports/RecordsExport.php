<?php

namespace Modules\Budget\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * @class RecordsExport
 * @brief Exporta datos de presupuesto
 *
 * Gestiona la exportación de datos de presupuesto
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RecordsExport implements FromView
{
    /**
     * Lista de datos a exportar
     *
     * @var array $data
     */
    protected $data;

    /**
     * Crea una nueva instancia de la clase
     *
     * @return array
     */
    public function __construct(array $view_data)
    {
        $this->data = $view_data;
    }

    /**
     * Muestra el reporte del registro solicitado
     *
     * @throws \Exception
     *
     * @return \Illuminate\View\View
     */
    public function view(): View
    {
        if ($this->data['report_type_id'] == "1") {
            return view('budget::pdf.budgetAnalyticMajor', [
                'records'        => $this->data['records'],
                'institution'    => $this->data['institution'],
                'currencySymbol' => $this->data['currencySymbol'],
                'fiscal_year'    => $this->data['fiscal_year'],
                'report_date'    => $this->data['report_date'],
                'initialDate'    => $this->data['initialDate'],
                'finalDate'      => $this->data['finalDate'],
                'profile'        => $this->data['profile'],
            ]);
        }

        if ($this->data['report_type_id'] == "2") {
            return view('budget::pdf.budgetAnalyticMajorAccrued', [
                'records'        => $this->data['records'],
                'institution'    => $this->data['institution'],
                'currencySymbol' => $this->data['currencySymbol'],
                'fiscal_year'    => $this->data['fiscal_year'],
                'report_date'    => $this->data['report_date'],
                'initialDate'    => $this->data['initialDate'],
                'finalDate'      => $this->data['finalDate'],
                'profile'        => $this->data['profile'],
            ]);
        }

        throw new \Exception('No se encontró una vista válida para retornar');
    }
}
