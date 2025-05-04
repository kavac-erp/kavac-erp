<?php

namespace Modules\Budget\Http\Controllers\Reports;

use App\Models\Currency;
use App\Models\Institution;
use App\Repositories\ReportRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\Budget\Models\BudgetModification;

/**
 * @class BudgetModificationReportsController
 * @brief Controlador de los reportes de presupuesto
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetModificationReportsController extends Controller
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
     * Genera el reporte de créditos adicionales
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    void
     */
    public function additionalCreditsPdf(int $id): void
    {
        $records = BudgetModification::where('type', 'C')->find($id);
        $institution = Institution::where('default', true)
            ->where('active', true)
            ->first();

        $currency = Currency::where('id', $records->currency_id)
            ->first() ?? Currency::where('default', true)
            ->first();

        $pdf = new ReportRepository();

        $config = [
            'institution' => $institution,
            'orientation' => 'P',
            'urlVerify' => url(''),
        ];
        $pdf->setConfig($config);

        $pdf->setHeader('Reporte de Créditos Adicionales');
        $pdf->setFooter();

        $bodyData = [
            'pdf' => $pdf,
            'records' => $records,
            'institution' => $institution,
            'currency' => $currency,
        ];
        $pdf->setBody('budget::pdf.additionalCredits', true, $bodyData);
    }

    /**
     * Genera el reporte de reducciones
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return    void
     */
    public function reductionsPdf(int $id): void
    {
        $records = BudgetModification::where('type', 'R')->find($id);
        $institution = Institution::where('default', true)
            ->where('active', true)
            ->first();
        $currency = Currency::where('default', true)->first();

        $pdf = new ReportRepository();

        $config = [
            'institution' => $institution,
            'orientation' => 'P',
            'urlVerify' => url(''),
        ];
        $pdf->setConfig($config);

        $pdf->setHeader('Reporte de Reducciones');
        $pdf->setFooter();

        $bodyData = [
            'pdf' => $pdf,
            'records' => $records,
            'institution' => $institution,
            'currency' => $currency,
        ];
        $pdf->setBody('budget::pdf.reductions', true, $bodyData);
    }

    /**
     * Genera el reporte de transferencias
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador de la transferencia
     *
     * @return    void
     */
    public function transfersPdf(int $id): void
    {
        $records = BudgetModification::where('type', 'T')->find($id);
        $array_accounts = [];

        $from_add = [
            'spac_description' => '',
            'code' => '',
            'description' => '',
            'amount' => '',
            'account_id' => '',
            'specific_action_id' => '',
        ];

        $to_add = [
            'spac_description' => '',
            'code' => '',
            'description' => '',
            'amount' => '',
            'account_id' => '',
            'specific_action_id' => '',
        ];

        $i = 0;

        foreach ($records->budgetModificationAccounts as $key => $account) {
            $item = explode('.', $account->budgetAccount->code);
            if ($item[2] != '00' && $item[3] != '00') {
                $sp = $account->budgetSubSpecificFormulation->specificAction;
                $spac_desc = $sp->specificable->code . ' - ' . $sp->code . ' | ' . $sp->name;
                $acc = $account->budgetAccount;
                $code = $acc->code;

                if ($account->operation === "D") {
                    $from_add = [
                        'spac_description' => $spac_desc,
                        'code' => $code,
                        'description' => $account->budgetAccount->denomination,
                        'amount' => $account->amount,
                        'account_id' => $acc->id,
                        'specific_action_id' => $sp->id,
                    ];
                } else {
                    $to_add = [
                        'spac_description' => $spac_desc,
                        'code' => $code,
                        'description' => $account->budgetAccount->denomination,
                        'amount' => $account->amount,
                        'account_id' => $acc->id,
                        'specific_action_id' => $sp->id,
                    ];
                }

                if (($key % 2) === 1) {
                    $array_accounts[$i] = [
                        'from_spac_description' => $from_add['spac_description'],
                        'from_code' => $from_add['code'],
                        'from_description' => $from_add['description'],
                        'from_amount' => $from_add['amount'],
                        'from_account_id' => $from_add['account_id'],
                        'from_specific_action_id' => $from_add['specific_action_id'],
                        'to_spac_description' => $to_add['spac_description'],
                        'to_code' => $to_add['code'],
                        'to_description' => $to_add['description'],
                        'to_amount' => $to_add['amount'],
                        'to_account_id' => $to_add['account_id'],
                        'to_specific_action_id' => $to_add['specific_action_id'],
                    ];
                    $i++;
                }
            }
        }

        $modification_accounts = $array_accounts;

        $institution = Institution::where('default', true)
            ->where('active', true)
            ->first();
        $currency = Currency::where('default', true)
            ->first();

        $pdf = new ReportRepository();

        $config = [
            'institution' => $institution,
            'orientation' => 'P',
            'urlVerify' => url(''),
        ];
        $pdf->setConfig($config);

        $pdf->setHeader('Reporte de Traspasos');
        $pdf->setFooter();

        $bodyData = [
            'pdf' => $pdf,
            'records' => $records,
            'modification_accounts' => $modification_accounts,
            'institution' => $institution,
            'currency' => $currency,
        ];
        $pdf->setBody('budget::pdf.transfers', true, $bodyData);
    }
}
