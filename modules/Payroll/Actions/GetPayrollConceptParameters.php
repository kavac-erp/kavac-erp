<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions;

use App\Models\Institution;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;

/**
 * @class GetPayrollConceptParameters
 * @brief Acciones para obtener los parámetros de conceptos
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class GetPayrollConceptParameters
{
    /**
     * Obtiene la lista de personal asignado en un concepto
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param    integer    $id    Identificador del concepto
     * @param    integer    $payroll_id    Identificador del personal
     * @param    boolean    $flag    Indica si se debe retornar el listado de los registros
     *
     * @return   \Illuminate\Http\JsonResponse|array|null    Listado de los registros a mostrar
     */
    public function getPayrollPersonalConceptAssign(
        int $id,
        ?int $payroll_id,
        bool $flag = false
    ) {
        $payroll = Payroll::find($payroll_id ?? null);
        $payrollConcept = PayrollConcept::whereId($id)->first();

        if (!$payrollConcept) {
            return null;
        }

        $payrollParameters = [];
        $exploded = multiexplode(
            [
                'if(', '(', ')', '{', '}',
                '==', '<=', '>=', '<', '>', '!=',
                '+', '-', '*', '/'
            ],
            $payrollConcept->getTranslateBackFormula()
        );
        foreach ($exploded as $explod) {
            /* Objeto asociado al modelo Parameter */
            $parameters = Parameter::where(
                [
                    'required_by' => 'payroll',
                    'active' => true,
                ]
            )->where('p_value', 'like', '%' . $explod . '%')->get();
            if ($parameters) {
                foreach ($parameters as $parameter) {
                    $jsonValue = json_decode($parameter->p_value);
                    if (isset($jsonValue->name)) {
                        if ($jsonValue->name == $explod) {
                            if ($jsonValue->parameter_type == 'global_value') {
                                /* Si el parámetro es de valor global */
                                array_push($payrollParameters, [
                                    'id' => $jsonValue->id,
                                    'name' => $jsonValue->name,
                                    'value' => $jsonValue->value
                                ]);
                            } elseif ($jsonValue->parameter_type == 'resettable_variable') {
                                if (!empty($payroll)) {
                                    $payrollParametersJson = json_decode($payroll?->payroll_parameters ?? '');
                                    $filteredParameters = collect($payrollParametersJson)
                                        ->where('name', $jsonValue->name)
                                        ->pluck('value', 'staff_id')
                                        ->toArray();
                                }
                                /* Si el parámetro es reiniciable a cero por período de nómina */
                                array_push($payrollParameters, [
                                    'id' => $jsonValue->id,
                                    'name' => $jsonValue->name,
                                    'value' => $filteredParameters ?? ''
                                ]);
                            } elseif ($jsonValue->parameter_type == 'processed_variable') {
                                /* Si el parámetro es una variable procesada */
                                array_push($payrollParameters, [
                                    'id' => $jsonValue->id,
                                    'name' => $jsonValue->name,
                                    'value' => $jsonValue->formula
                                ]);
                            } elseif ($jsonValue->parameter_type == 'time_parameter' && $flag) {
                                /* Si el parámetro es una variable procesada */
                                array_push($payrollParameters, [
                                    'id' => $jsonValue->id,
                                    'name' => $jsonValue->name,
                                    'value' => $jsonValue->formula
                                ]);
                            }
                        }
                    }
                }
            }
        }
        if (empty($payrollParameters)) {
            return null;
        };
        $payrollParameters = collect($payrollParameters)->unique('id')->values()->toArray();
        $payrollParametersRep = new PayrollAssociatedParametersRepository();
        $assignTo = $payrollParametersRep->loadData('assignTo');
        $extraOptions = [];
        foreach ($payrollConcept->payrollConceptAssignOptions->where('key', 'staff') as $assign_option) {
            $extraOptions[$payrollConcept->id][] = $assign_option['assignable_id'];
        }
        $exceptionStaffs = array_unique(array_merge(...$extraOptions));
        $institution = auth()->user()?->profile?->institution ?? Institution::query()
            ->where('active', true)
            ->where('default', true)
            ->first();
        /* Se obtienen todos los trabajadores asociados a la institución y se evalua si aplica cada uno de los conceptos */
        $payrollStaffs = PayrollStaff::query()
            ->whereHas('payrollEmployment', function ($q) use ($institution) {
                $q->where('active', 't')->whereHas('department', function ($qq) use ($institution) {
                    $qq->where('institution_id', $institution->id);
                });
            })
            ->orWhereIn('id', $exceptionStaffs)
            ->get()
            ->map(function ($staff) use ($payrollConcept, $assignTo, $extraOptions) {
                $conceptAssignTo = json_decode($payrollConcept->assign_to);
                if (in_array($staff->id, $extraOptions[$payrollConcept->id] ?? [])) {
                    $verify = true;
                } elseif ($payrollConcept->is_strict ?? false) {
                    if (count($conceptAssignTo) > 1) {
                        $conceptAssignTo = array_filter($conceptAssignTo, function ($item) {
                            return $item->id !== 'staff';
                        });
                    }
                    $conceptAssignTo = array_chunk($conceptAssignTo, 1);
                    $verify = true;
                    foreach ($conceptAssignTo as $key => $value) {
                        if (
                            false == verify_assignment(
                                $value,
                                $assignTo,
                                $payrollConcept->payrollConceptAssignOptions,
                                $staff->id
                            )
                        ) {
                            $verify = false;
                        };
                    }
                } else {
                    $verify = verify_assignment(
                        $conceptAssignTo,
                        $assignTo,
                        $payrollConcept->payrollConceptAssignOptions,
                        $staff->id
                    );
                }
                return $verify !== false ? [
                    'id' => $staff->id,
                    'name' => $staff->first_name . ' ' . $staff->last_name,
                ] : null;
            })
            ->filter()
            ->toArray();

        if ($flag) {
            return [
                'record' => [
                    'id' => $payrollConcept->id,
                    'name' => $payrollConcept->name,
                    'parameters' => $payrollParameters,
                ]
            ];
        }

        return response()->json([
            'record' => [
                'id' => $payrollConcept->id,
                'name' => $payrollConcept->name,
                'parameters' => $payrollParameters,
                'staffs' => array_values($payrollStaffs),
            ]
        ], 200);
    }
}
