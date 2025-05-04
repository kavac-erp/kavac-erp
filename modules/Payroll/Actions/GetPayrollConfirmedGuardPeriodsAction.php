<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions;

use Modules\Payroll\Models\PayrollGuardSchemePeriod;
use Modules\Payroll\Models\PayrollTimeSheetParameter;

final class GetPayrollConfirmedGuardPeriodsAction
{
    public function __construct()
    {
    }

    public function invoke(
        string $fromDate,
        string $toDate,
        $payrollSupervisedGroupId,
        $payrollTimeSheetParameterId,
        $instituionId
    ): ?array
    {
        $parameters = PayrollTimeSheetParameter::query()
            ->find($payrollTimeSheetParameterId)
            ->payrollParameterTimeSheetParameters
            ->map(function ($item) {
                $data = json_decode($item->parameter->p_value);
                if ($data) {
                    return $data->acronym . ' - ' . $data->name;
                }
            })
            ->unique()
            ->toArray();

        $payrollGuardPeriods = PayrollGuardSchemePeriod::query()
            ->whereHas('payrollGuardScheme', function ($query) use ($payrollSupervisedGroupId, $instituionId) {
                $query->where('payroll_supervised_group_id', $payrollSupervisedGroupId);
            })
            ->whereHas('documentStatus', function ($query) {
                $query->where('action', 'CE');
            })
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->where(function ($query) use ($fromDate) {
                    $query->where('from_date', '<=', $fromDate)
                        ->where('to_date', '>=', $fromDate);
                })
                ->orWhere(function ($query) use ($fromDate, $toDate) {
                    $query->where('from_date', '>=', $fromDate)
                        ->where('to_date', '<=', $toDate);
                })
                ->orWhere(function ($query) use ($toDate) {
                    $query->where('from_date', '<=', $toDate)
                        ->where('to_date', '>=', $toDate);
                });
            })
            ->get()
            ?->map(function ($period) {
                return $period->getData() ?? [];
            })
            ?->unique(function ($item) {
                return array_keys($item);
            });
        $confirmed = [];
        $result = $payrollGuardPeriods?->reduce(function ($carry, $period) use ($fromDate, $toDate, $parameters, &$confirmed) {
            foreach ($period as $key => $value) {
                if ($key >= $fromDate && $key <= $toDate) {
                    foreach ($value as $turno => $val) {
                        $lastIndex = strrpos($turno, "-");
                        $index = trim(substr($turno, 0, $lastIndex));
                        $staffId = trim(substr($turno, $lastIndex + 1));

                        if (in_array($index, $parameters)) {
                            if (!isset($carry[$turno])) {
                                $carry[$turno] = $val['count'];
                                $confirmed[$index] = $val['confirmed'];
                            } else {
                                $carry[$turno] += $val['count'];
                                $confirmed[$index] = $val['confirmed'] && $confirmed[$index];
                            }
                        }
                    }
                }
            }

            return $carry;
        }, null);

        return [
            'confirmed' => array_keys(array_filter($confirmed, function($value) {
                return $value === true;
            })),
            'result' => $result
        ];
    }
}
