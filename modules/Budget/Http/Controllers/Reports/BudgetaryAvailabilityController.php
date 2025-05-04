<?php

namespace Modules\Budget\Http\Controllers\Reports;

use App\Models\FiscalYear;
use App\Models\Institution;
use App\Models\Parameter;
use App\Models\Receiver;
use App\Repositories\ReportRepository;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Purchase\Models\PurchaseBaseBudget;

/**
 * @class BudgetaryAvailabilityController
 *
 * @brief Controlador que gestiona los reportes PDF de la Disponibilidad Presupuestaria.
 *
 * Clase que gestiona de la generación de reportes de Disponibilidad Presupuestaria
 *
 * @author Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetaryAvailabilityController extends Controller
{
    /**
     * Método que envía los datos a la plantilla del reporte PDF.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param $id del registro
     * @param $module Módulo a verificar para ser agregado al reporte
     *
     * @return void
     */
    public function pdf($id, $module)
    {
        $payroll = null;
        $record = PurchaseBaseBudget::with(
            'currency',
            'purchaseRequirement.preparedBy.payrollStaff',
            'purchaseRequirement.reviewedBy.payrollStaff',
            'purchaseRequirement.verifiedBy.payrollStaff',
            'purchaseRequirement.firstSignature.payrollStaff',
            'purchaseRequirement.secondSignature.payrollStaff',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'preparedBy.payrollStaff',
            'reviewedBy.payrollStaff',
            'verifiedBy.payrollStaff',
            'firstSignature.payrollStaff',
            'secondSignature.payrollStaff'
        )->find($id);

        if ($module == 'Payroll') {
            $payroll = Payroll::with([
                'payrollPaymentPeriod.payrollPaymentType.payrollConcepts.currency',
                'payrollPaymentPeriod.payrollPaymentType.payrollConcepts.budgetAccount'
            ])->find($id);

            $round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
            $number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
            $nameDecimalFunction = $round->p_value == 'false' ? 'currency_format' : 'round';
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
            $records = $payroll->payrollStaffPayrolls()
                ->select('concept_type', 'payroll_staff_id')
                ->get()
                ->map(function ($record) {
                    return [
                        'payroll_staff' => [
                            'id' => $record->payrollStaff->id,
                            'name' => $record->payrollStaff->fullName,
                        ],
                        'concept_type' => $record->concept_type,
                    ];
                })
                ->toArray();

            $accounts = [];
            $totalAmount = 0;
            $budgetSpecificActionController = new \Modules\Budget\Http\Controllers\BudgetSpecificActionController();

            $itemIds = [];
            $items = [];
            $allBudgetAccounts = [];
            $budgetAccounts = [];
            $budgetSpecificActions = [];

            array_map(function ($record) use (&$itemIds, &$items) {
                return array_map(function ($conceptType) use (&$itemIds, &$items) {
                    return array_map(function ($item) use (&$itemIds, &$items) {
                        if ($item['sign'] != '-' && !in_array($item['name'], $itemIds)) {
                            $itemIds[] = $item['id'];
                        }
                        if ($item['sign'] != '-') {
                            $items[] = $item;
                        }
                    }, $conceptType);
                }, $record['concept_type']);
            }, $records);

            $concepts = PayrollConcept::query()
                ->whereIn('id', $itemIds)
                ->toBase()
                ->get();

            foreach ($concepts as $concept) {
                $accs = $budgetSpecificActionController->getOpenedAccounts($concept->budget_specific_action_id, $currentFiscalYear->year . '-12-31');
                $accountFiltered = array_filter($accs->getData()->records, function ($account) use ($concept) {
                    return $account->id != '' && $account->id == $concept->budget_account_id;
                });
                $allBudgetAccounts[$concept->name] = reset($accountFiltered)->amount ?? 0.00;

                $budgetAccounts[$concept->name] = $concept->budget_account_id ? \Modules\Budget\Models\BudgetAccount::find($concept->budget_account_id) : null;
                $budgetSpecificActions[$concept->name] = $concept->budget_specific_action_id ? \Modules\Budget\Models\BudgetSpecificAction::find($concept->budget_specific_action_id) : null;
            }

            foreach ($items as $value) {
                $concept = $concepts->where('id', $value['id'])->first();

                if ($value['sign'] != '-' && $budgetAccounts[$concept->name] && $concept->budget_specific_action_id) {
                    if ($value['value'] > 0) {
                        if (!isset($accounts[$value['name']])) {
                            $accounts[$value['name']]['id'] = $concept->id;
                            $accounts[$value['name']]['type'] = $value['sign'];
                            $accounts[$value['name']]['value'] = 0;
                            $accounts[$value['name']]['budget_account_code'] = $budgetAccounts[$concept->name]->code;
                            $accounts[$value['name']]['budget_specific_action_id'] = $concept->budget_specific_action_id;
                            $accounts[$value['name']]['budget_specific_action_desc'] = $budgetSpecificActions[$concept->name]->description;
                            $accounts[$value['name']]['budget_account_id'] = $concept->budget_account_id;
                            $accounts[$value['name']]['budget_account_amount'] = $allBudgetAccounts[$concept->name];
                        }
                        $accounts[$value['name']]['value'] += $nameDecimalFunction($value['value'], $number_decimals->p_value);
                        $totalAmount += $nameDecimalFunction($value['value'], $number_decimals->p_value);
                    }
                }
            }
        }

        /* Base para generar el pdf */
        $pdf = new ReportRepository();

        $pdf->setConfig(
            [
                'institution' => Institution::first(),
                'urlVerify' => url('/budget/budgetary_availability/pdf/' . $id),
            ]
        );

        $pdf->setHeader('Reporte de Disponibilidad Presupuestaria');

        $pdf->setFooter();

        $pdf->setBody('budget::pdf.BudgetaryAvailability', true, [
            'pdf' => $pdf,
            'record' => $payroll != null ? $payroll : $record,
            'accounts' => $payroll != null ? $accounts : '',
            'totalAmount' => $payroll != null ? $totalAmount : '',
            'module' => $module
        ]);
    }
}
