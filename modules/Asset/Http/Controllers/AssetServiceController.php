<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Routing\Controller;
use Module;

/*
 * @class ServiceController
 * @brief Controlador de Servicios del Módulo de Bienes
 *
 * Clase que gestiona los registros utilizados en los elemnetos del tipo select2
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class AssetServiceController extends Controller
{
    public function getPayrollStaffs()
    {
        return (Module::has('Payroll')) ?
            template_choices(
                'Modules\Payroll\Models\PayrollStaff',
                ['id_number', '-', 'full_name'],
                ['relationship' => 'PayrollEmployment', 'where' => ['active' => true]],
                true
            ) : [];
    }
    // public function getPayrollPositionTypes()
    // {
    //     return (Module::has('Payroll')) ?
    //         template_choices('Modules\Payroll\Models\PayrollPositionType', 'name', '', true) : [];
    // }
    // public function getPayrollPositions()
    // {
    //     return (Module::has('Payroll')) ?
    //         template_choices('Modules\Payroll\Models\PayrollPosition', 'name', '', true) : [];
    // }
    public function getPayrollStaffInfo($id)
    {
        $payroll_position_id = [];
        $payroll_positions = \Modules\Payroll\Models\PayrollEmployment::where('payroll_staff_id', $id)->get();
        array_push($payroll_position_id, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($payroll_positions as $value) {
            array_push($payroll_position_id, [
                'id' => $value->payrollPosition->id,
                'text' => $value->payrollPosition->id
            ]);
        }

        $payroll_type_id = (Module::has('Payroll')) ?
        template_choices(
            'Modules\Payroll\Models\PayrollEmployment',
            ['payroll_position_type_id'],
            ['payroll_staff_id' => $id],
            true
        ) : [];

        $payroll_department_id = (Module::has('Payroll')) ?
        template_choices(
            'Modules\Payroll\Models\PayrollEmployment',
            ['department_id'],
            ['payroll_staff_id' => $id],
            true
        ) : [];

        $payroll_position_name = (Module::has('Payroll')) ?
            template_choices(
                'Modules\Payroll\Models\PayrollPosition',
                'name',
                ['id' => (int)$payroll_position_id[1]["text"]],
                true
            ) : [];

        $payroll_position_type_name = (Module::has('Payroll')) ?
            template_choices(
                'Modules\Payroll\Models\PayrollPositionType',
                'name',
                ['id' => (int)$payroll_type_id[1]["text"]],
                true
            ) : [];
            // dd($payroll_position_name, $payroll_position_type_name);
        $department_name = (Module::has('Payroll')) ?
            template_choices(
                'Modules\Payroll\Models\Department',
                'name',
                ['id' => (int)$payroll_department_id[1]["text"]],
                true
            ) : [];

        return [$payroll_position_name[1], $payroll_position_type_name[1], $department_name[1]];
    }
}
