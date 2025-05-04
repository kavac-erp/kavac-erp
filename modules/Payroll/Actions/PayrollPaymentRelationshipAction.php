<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions;

use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\Payroll;

final class PayrollPaymentRelationshipAction
{
    public function __construct()
    {
    }

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

                if (str_contains($key, 'Ficha-')) continue;

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
                        $exists = array_reduce($carry, function($subCarry, $item) use ($newItem) {
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
