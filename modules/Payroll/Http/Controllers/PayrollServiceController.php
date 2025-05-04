<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * @class PayrollServiceController
 * @brief Controlador de Servicios del Módulo de Nómina
 *
 * Clase que gestiona los registros utilizados en los elemnetos del tipo select2
 *
 * @author Henry Paredes <henryp2804@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollServiceController extends Controller
{
    /**
     * Listado de personal
     *
     * @return array
     */
    public function getStaffs()
    {
        return template_choices('Modules\Payroll\Models\PayrollStaff', ['id_number','-','full_name'], '', true);
    }

    /**
     * Listado de grados de instrucción
     *
     * @return array
     */
    public function getInstructionDegrees()
    {
        return template_choices('Modules\Payroll\Models\PayrollInstructionDegree', 'name', '', true);
    }
}
