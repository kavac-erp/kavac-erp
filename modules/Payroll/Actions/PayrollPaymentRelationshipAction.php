<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions;

use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\Payroll;

/**
 * @class PayrollPaymentRelationshipAction
 * @brief Acciones para los pagos de relaciones
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class PayrollPaymentRelationshipAction
{
    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Obtiene los parámetros de nómina para un período de pago específico.
     *
     * @param integer $payrollId Identificador del período de pago.
     * @param bool $allParameters Indica si se deben retornar todos los parámetros o solo los activos y/o pendientes.
     *
     * @return array Lista de parámetros de nómina.
     */
    public function getPayrollParameters(int $payrollId, bool $allParameters = true)
    {
        $payroll = Payroll::query()
            ->find($payrollId);

        $timeParameter = array_values(
            $payroll->payrollPaymentPeriod
                ?->payrollPaymentType
                ->payrollTimeSheetParameters
                ->reduce(function ($carry, $parameter) use ($payroll) {
                    $this->addParameterToCarry($carry, $parameter, $payroll, 'active');
                    $this->addParameterToCarry($carry, $parameter, $payroll, 'pending');

                    return $carry;
                }, [])
        );

        return $allParameters
            ? array_merge($timeParameter ?? [], json_decode($payroll->payroll_parameters) ?? [])
            : $timeParameter ?? [];
    }

    /**
     * Agrega un parámetro a la variable de carry en función de los datos de la hoja de tiempo.
     *
     * @param array $carry Variable de carry que se utilizará para almacenar los parámetros.
     * @param object $parameter Objeto que contiene los parámetros.
     * @param object $payroll Objeto que contiene la información de la nómina.
     * @param string $typeTimeSheet Tipo de hoja de tiempo (activo o pendiente).
     *
     * @return void
     */
    protected function addParameterToCarry(&$carry, $parameter, $payroll, $typeTimeSheet = 'active')
    {
        $payrollTimeSheets = ('active' === $typeTimeSheet)
            ? $parameter?->payrollTimeSheets()
                ->whereHas('documentStatus', function ($query) {
                    $query->where('action', 'CE');
                })
                ->where([
                    'from_date' => $payroll->payrollPaymentPeriod->start_date,
                    'to_date' => $payroll->payrollPaymentPeriod->end_date,
                ])
                ->get()
            : $parameter?->payrollTimeSheetsPending()
                ->whereHas('documentStatus', function ($query) {
                    $query->where('action', 'CE');
                })
                ->where([
                    'from_date' => $payroll->payrollPaymentPeriod->start_date,
                    'to_date' => $payroll->payrollPaymentPeriod->end_date,
                ])
                ->get();

        foreach ($payrollTimeSheets ?? [] as $payrollTimeSheet) {
            $timeSheetData = $payrollTimeSheet?->time_sheet_data ?? [];

            foreach ($timeSheetData as $key => $value) {
                list($pKey, $pNameStaff) = explode('-', $key);

                if ('total' === $pKey) {
                    continue;
                }
                if ('Conceptos' === $pKey) {
                    continue;
                }
                if ('Observación' === $pKey) {
                    continue;
                }

                if (str_contains($key, 'Ficha-')) {
                    continue;
                }

                $lastIndex = strrpos($key, '-');

                if ($lastIndex !== false) {
                    $beforeHyphen = trim(substr($key, 0, $lastIndex));

                    if ('subtotal' === $beforeHyphen) {
                        continue;
                    }

                    $pStaff = trim(substr($key, $lastIndex + 1));
                    list($pKey, $pName) = explode(' - ', $beforeHyphen);

                    $newKey = $pKey;
                    $parameterCurrent = Parameter::query()
                        ->where(
                            [
                                'active' => true,
                                'required_by' => 'payroll',
                            ]
                        )
                        ->where('p_key', 'like', 'global_parameter_%')
                        ->where('p_value', 'like', '%time_parameter%')
                        ->toBase()
                        ->get()
                        ->filter(function ($parameter) use ($newKey) {
                            $pValue = json_decode($parameter->p_value);
                            return $pValue->acronym === $newKey;
                        })
                        ->first();

                    if ($parameterCurrent) {
                        $pValue = json_decode($parameterCurrent->p_value);
                        $newItem = [
                            'id' => $pValue->id,
                            'name' => $pName,
                            'staff_id' => (int) $pStaff,
                            'value' => $value,
                            'time_sheet' => $typeTimeSheet
                        ];
                        $exists = array_reduce($carry, function ($subCarry, $item) use ($newItem) {
                            return $subCarry || ($item['id'] == $newItem['id'] && $item['staff_id'] == $newItem['staff_id'] && $item['time_sheet'] == $newItem['time_sheet']);
                        }, false);

                        if (!$exists) {
                            $carry[] = $newItem;
                        }
                    }
                }
            }
        }
    }
}
