<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;

/**
 * @class WarehouseServiceController
 * @brief Controlador de Servicios del Módulo de Almacén
 *
 * Clase que gestiona los registros utilizados en los elemnetos del tipo select2
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseServiceController extends Controller
{
    /**
     * Obtiene un listado del personal
     *
     * @return array
     */
    public function getPayrollStaffs()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? template_choices('Modules\Payroll\Models\PayrollStaff', ['id_number', '-', 'full_name'], '', true) : [];
    }

    /**
     * Obtiene un listado de cargos
     *
     * @return array
     */
    public function getPayrollPositions()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? template_choices('Modules\Payroll\Models\PayrollPosition', 'name', '', true) : [];
    }

    /**
     * Obtiene un listado de los proyectos de presupuesto si el módulo esta presente
     *
     * @param integer|null $id Identificador del proyecto
     *
     * @return array
     */
    public function getBudgetProjects($id = null)
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? template_choices(
            'Modules\Budget\Models\BudgetProject',
            ['code', '-', 'name'],
            ($id) ? ['id' => $id] : [],
            true
        ) : [];
    }

    /**
     * Obtiene un listado de acciones centralizadas de presupuesto si el modulo esta presente
     *
     * @param integer|null $id Identificador de la accion centralizada
     *
     * @return array
     */
    public function getBudgetCentralizedActions($id = null)
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? template_choices(
            'Modules\Budget\Models\BudgetCentralizedAction',
            ['code', '-', 'name'],
            ($id) ? ['id' => $id] : [],
            true
        ) : [];
    }

    /**
     * Obtiene un listado de las acciones especificas de presupuesto si el modulo esta presente
     *
     * @param string $type Tipo de acción específica
     * @param integer $id Identificador del poyecto o acción centralizada
     * @param mixed|null $source Fuente de donde se realiza la consulta
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getBudgetSpecificActions($type, $id, $source = null)
    {
        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            /* Arreglo con información de las acciones específicas */
            $data = [['id' => '', 'text' => 'Seleccione...']];

            if ($type === "Project") {
                /* Objeto con las acciones específicas asociadas a un proyecto */
                $specificActions = \Modules\Budget\Models\BudgetProject::find($id)->specificActions()->get();
            } elseif ($type == "CentralizedAction") {
                /* Objeto con las acciones específicas asociadas a una acción centralizada */
                $specificActions = \Modules\Budget\Models\BudgetCentralizedAction::find($id)->specificActions()->get();
            }

            foreach ($specificActions as $specificAction) {
                /* Objeto que determina si la acción específica ya fue formulada para el último presupuesto */
                $existsFormulation = \Modules\Budget\Models\BudgetSubSpecificFormulation::where([
                    'budget_specific_action_id' => $specificAction->id
                ])->orderBy('year', 'desc')->first();

                if ($existsFormulation) {
                    array_push($data, [
                        'id' => $specificAction->id,
                        'text' => $specificAction->code . " - " . $specificAction->name
                    ]);
                }
            }

            return response()->json($data);
        } else {
            return [];
        }
    }
}
