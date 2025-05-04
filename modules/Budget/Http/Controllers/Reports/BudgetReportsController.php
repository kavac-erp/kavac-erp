<?php

namespace Modules\Budget\Http\Controllers\Reports;

use App\Models\Profile;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Budget\Models\Currency;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\ReportRepository;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\BudgetAccount;
use Modules\Budget\Models\BudgetProject;
use Modules\Budget\Exports\RecordsExport;
use Illuminate\Contracts\Support\Renderable;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Modules\Budget\Models\BudgetCentralizedAction;
use Modules\Budget\Models\BudgetModificationAccount;
use Modules\Budget\Jobs\CreateBudgetAnalyticalMajorJob;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class BudgetAccountOpenController
 * @brief Clase para generar reporte de disponibilad presupuestaria
 *
 * Clase para generar reporte de disponibilad presupuestaria
 *
 * @author Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetReportsController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:budget.analyticalmajor.index', ['only' => 'budgetAnalyticalMajor']);
        $this->middleware('permission:budget.budgetavailability.index', ['only' => 'budgetAvailability']);
        $this->middleware('permission:budget.formulated.index', ['only' => 'getFormulatedView']);
    }

    /**
     * Genera los datos necesarios para el formulario de generacion de reporte de disponibilidad presupuestaria
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
     *
     * @return    Renderable|array
     */
    public function budgetAvailability($flag = null)
    {
        $budgetItems = $this->getBudgetAccounts();
        $budgetProjects = $this->getBudgetProjects(true);
        $budgetCentralizedActions = $this->getBudgetCentralizedActions(true);

        $data = array();
        $temp = array('text' => '', 'children' => []);
        $isFirst = true;

        foreach ($budgetItems as $budgetItem) {
            $code = str_replace('.', '', $budgetItem->getCodeAttribute());

            if (substr_count($code, '0') == 8) {
                if (!$isFirst) {
                    array_push($data, $temp);
                    $temp = array('text' => '', 'children' => []);
                }

                $temp['text'] = $budgetItem->denomination;
                $isFirst = false;
            }

            array_push($temp['children'], array(
                'text' => $budgetItem->denomination . ' ' . "($code)",
                'id' => (int) $code,
            ));
        }

        array_push($data, $temp);

        if ($flag) {
            return [
                'budgetItems' => json_encode($data),
                'budgetProjects' => json_encode($budgetProjects),
                'budgetCentralizedActions' => json_encode($budgetCentralizedActions),
            ];
        }

        return view('budget::reports.budgetAvailability', [
            'budgetItems' => json_encode($data),
            'budgetProjects' => json_encode($budgetProjects),
            'budgetCentralizedActions' => json_encode($budgetCentralizedActions),
        ]);
    }

    /**
     * Metodo para retornar un array con las cuentas presupuestarias
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
     *
     * @return    array Arreglo ordenado de cuentas presupuestarias
     */
    public function getBudgetAccounts()
    {

        $budgetItems = BudgetAccount::all()->all();

        usort($budgetItems, function ($budgetItemOne, $budgetItemTwo) {

            $codeOne = str_replace('.', '', $budgetItemOne->getCodeAttribute());
            $codeTwo = str_replace('.', '', $budgetItemTwo->getCodeAttribute());

            if ($codeOne > $codeTwo) {
                return 1;
            } elseif ($codeOne == $codeTwo) {
                return 0;
            } else {
                return -1;
            }
        });

        return $budgetItems;
    }

    /**
     * Metodo para retornar un array con las cuentas presupuestarias para el reporte
     *
     * @author  Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param   bool    $accountsWithMovements Indica si se consultan solo las cuentas con movimientos
     * @param   object  $project Datos del proyecto
     * @param   string  $initialDate Fecha inicial de consulta
     * @param   string  $finalDate Fecha final de consulta
     *
     * @return  array Arreglo ordenado de cuentas presupuestarias para el reporte
     */
    public function getBudgetAccountsOpen(bool $accountsWithMovements, object $project, string $initialDate, string $finalDate)
    {
        ini_set('max_execution_time', 600);
        $project_accounts_open = array();
        $compromised = 0;

        foreach ($project->specificActions as $specificAction) {
            $finalAccounts = [];

            $accounts = BudgetAccount::query()
                ->with(['accountOpens' => function ($query) use ($specificAction) {
                    $query->where('budget_sub_specific_formulation_id', $specificAction->subSpecificFormulations[0]->id);
                }, 'accountParent'])
                ->whereHas('accountOpens', function ($query) use ($initialDate, $finalDate) {
                    $query
                        ->with('subSpecificFormulation')
                        ->whereHas('subSpecificFormulation', function ($query) use ($initialDate, $finalDate) {
                            $query
                                ->where('date', '>=', $initialDate)
                                ->where('date', '<=', $finalDate);
                        });
                })
                ->get();

            $modificationAccounts = BudgetAccount::query()
                ->with(['modificationAccounts' => function ($query) use ($specificAction) {
                    $query->where('budget_sub_specific_formulation_id', $specificAction->subSpecificFormulations[0]->id);
                }, 'accountParent', 'modificationAccounts.budgetSubSpecificFormulation.accountOpens',
                'modificationAccounts.budgetModification'])
                ->whereHas('modificationAccounts.budgetModification', function ($query) use ($initialDate, $finalDate) {
                    $query
                        ->where('approved_at', '>=', $initialDate)
                        ->where('approved_at', '<=', $finalDate);
                })
                ->get();

            $formFormId = [];
            $formComKey = [];
            $formCauKey = [];
            $formPaidKey = [];

            foreach ($accounts as $account) {
                $arrayMod = [];

                if (isset($account['accountOpens']) && isset($account['accountOpens'][0])) {
                    if (!array_key_exists($account->code, $finalAccounts)) {
                        $account['self_amount'] = $account['accountOpens'][0]['total_year_amount'];
                        $account['compromised'] = 0;
                        $account['caused'] = 0;
                        $account['paid'] = 0;
                        $account['increment'] = 0;
                        $account['date'] = $account['accountOpens'][0]['subSpecificFormulation']['date'] ??
                            $account['accountOpens'][0]['created_at'];

                        if (!in_array($account['accountOpens'][0]['id'], $formFormId)) {
                            array_push($formFormId, $account['accountOpens'][0]['id']);
                            $compromisedAmount = (array)$this->getAccountCompromisedAmout($account['accountOpens'][0], $initialDate, $finalDate);
                            $causedAmount = (array)$this->getAccountCompromisedCausedAmount($account['accountOpens'][0], $initialDate, $finalDate);
                            $paidAmount = (array)$this->getAccountCompromisedPaidAmount($account['accountOpens'][0], $initialDate, $finalDate);
                        } else {
                            $compromisedAmount = [];
                            $causedAmount = [];
                            $paidAmount = [];
                        }

                        if (count($compromisedAmount) > 0) {
                            foreach ($compromisedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => $value['amount'],
                                    'caused' => 0,
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(1, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $value['description']
                                ];

                                if (!in_array($key . $account['accountOpens'][0]['budget_account_id'], $formComKey)) {
                                    array_push($formComKey, $key . $account['accountOpens'][0]['budget_account_id']);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($causedAmount) > 0) {
                            foreach ($causedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => $value['amount'],
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(2, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $account['accountOpens'][0]['budget_account_id'], $formCauKey)) {
                                    array_push($formCauKey, $key . $account['accountOpens'][0]['budget_account_id']);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($paidAmount) > 0) {
                            foreach ($paidAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => 0,
                                    'paid' => $value['amount'],
                                    'date' => $value['date']->setTime(3, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $account['accountOpens'][0]['budget_account_id'], $formPaidKey)) {
                                    array_push($formPaidKey, $key . $account['accountOpens'][0]['budget_account_id']);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        $account['modifications'] = collect($arrayMod)->sortBy('date')->toArray();
                        $finalAccounts[$account->code] = $account;
                    }
                }
            }

            $arrayId = [];
            $arrayComKey = [];
            $arrayCauKey = [];
            $arrayPaidKey = [];

            foreach ($modificationAccounts as $modificationAccount) {
                if (isset($modificationAccount['modificationAccounts']) && isset($modificationAccount['modificationAccounts'][0])) {
                    $arrayMod = [];
                    foreach ($modificationAccount['modificationAccounts'] as $modAccount) {
                        if ($modAccount['operation'] == 'I') {
                            $data = [
                                'current' => $modAccount['amount'],
                                'self_available' => $modAccount['amount'],
                                'increment' => $modAccount['amount'],
                                'decrement' => 0,
                                'compromised' => 0,
                                'caused' => 0,
                                'paid' => 0,
                                'date' => $modAccount['budgetModification']['approved_at'],
                                'increment_descriptions' => $modAccount['budgetModification']['description']
                            ];
                            array_push($arrayMod, $data);
                        } else {
                            $data = [
                                'current' => $modAccount['amount'],
                                'self_available' => $modAccount['amount'],
                                'increment' => 0,
                                'decrement' => $modAccount['amount'],
                                'compromised' => 0,
                                'caused' => 0,
                                'paid' => 0,
                                'date' => $modAccount['budgetModification']['approved_at'],
                                'decrement_descriptions' => $modAccount['budgetModification']['description']
                            ];
                            array_push($arrayMod, $data);
                        }

                        if (!in_array($modAccount->id, $arrayId)) {
                            array_push($arrayId, $modAccount->id);
                            $compromisedAmount = (array)$this->getAccountCompromisedAmout($modAccount, $initialDate, $finalDate);
                            $causedAmount = (array)$this->getAccountCompromisedCausedAmount($modAccount, $initialDate, $finalDate);
                            $paidAmount =  (array)$this->getAccountCompromisedPaidAmount($modAccount, $initialDate, $finalDate);
                        } else {
                            $compromisedAmount = [];
                            $causedAmount = [];
                            $paidAmount = [];
                        }

                        if (count($compromisedAmount) > 0) {
                            foreach ($compromisedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => $value['amount'],
                                    'caused' => 0,
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(1, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $value['description']
                                ];

                                if (!in_array($key . $modAccount->budget_account_id, $arrayComKey)) {
                                    array_push($arrayComKey, $key . $modAccount->budget_account_id);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($causedAmount) > 0) {
                            foreach ($causedAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => $value['amount'],
                                    'paid' => 0,
                                    'date' => $value['date']->setTime(2, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $modAccount->budget_account_id, $arrayCauKey)) {
                                    array_push($arrayCauKey, $key . $modAccount->budget_account_id);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (count($paidAmount) > 0) {
                            foreach ($paidAmount as $key => $value) {
                                $data = [
                                    'current' => $value['amount'],
                                    'self_available' => $value['amount'],
                                    'increment' => 0,
                                    'decrement' => 0,
                                    'compromised' => 0,
                                    'caused' => 0,
                                    'paid' => $value['amount'],
                                    'date' => $value['date']->setTime(3, 0)->format('Y-m-d H:i:s'),
                                    'decrement_descriptions' => $compromisedAmount[$key]['description']
                                ];

                                if (!in_array($key . $modAccount->budget_account_id, $arrayPaidKey)) {
                                    array_push($arrayPaidKey, $key . $modAccount->budget_account_id);
                                    array_push($arrayMod, $data);
                                }
                            }
                        }

                        if (array_key_exists($modificationAccount->code, $finalAccounts)) {
                            foreach ($modAccount['budgetSubSpecificFormulation']['accountOpens'] as $accsOpen) {
                                if ($accsOpen['budget_account_id'] == $modificationAccount['id']) {
                                    $modificationAccount['date'] = $accsOpen->subSpecificFormulation->date ??
                                        $accsOpen['created_at'];
                                    $modificationAccount['self_amount'] = $accsOpen['total_year_amount'];
                                }
                            };
                        }

                        $modificationAccount['modifications'] = collect($arrayMod)->sortBy('date')->toArray();
                        $finalAccounts[$modificationAccount->code] = $modificationAccount;
                    }
                }
            }

            $parentsArray = [];

            foreach ($finalAccounts as $finalAccount) {
                $parents = $this->getAccountParents($finalAccount, []);
                array_push($parentsArray, $parents);
            }

            foreach ($parentsArray as $pArray) {
                foreach ($pArray as $pA) {
                    if (!array_key_exists($pA->code, $finalAccounts)) {
                        $finalAccounts[$pA->code] = $pA;
                    }
                }
            }

            krsort($finalAccounts);

            foreach ($finalAccounts as $finalAccount) {
                if ($finalAccount->parent_id != null) {
                    $finalAccountParent = $finalAccount->getParent(
                        $finalAccount->group,
                        $finalAccount->item,
                        $finalAccount->generic,
                        $finalAccount->specific,
                        $finalAccount->subspecific
                    );

                    if ($finalAccount->generic == 00) {
                        $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment_total'] ??
                            $finalAccount['increment'];
                        $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement_total'] ??
                            $finalAccount['decrement'];
                        $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised_total'] ??
                            $finalAccount['compromised'];
                        $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['compromised_caused'] ??
                            $finalAccount['caused'];
                        $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['compromised_paid'] ??
                            $finalAccount['paid'];
                        $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'] ??
                            $finalAccount['accountOpens'][0]['created_at'] ??
                            $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'];

                        if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                            foreach ($finalAccount['modifications'] as $mod) {
                                $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment_total'] ??
                                    $mod['increment'];
                                $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement_total'] ??
                                    $mod['decrement'];
                                $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised_total'] ??
                                    $mod['compromised'];
                                $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['compromised_caused'] ??
                                    $mod['caused'];
                                $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['compromised_paid'] ??
                                    $mod['paid'];
                                $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                            }
                        }
                    } elseif ($finalAccount->specific == 00) {
                        $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment_total'] ??
                            $finalAccount['increment'];
                        $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement_total'] ??
                            $finalAccount['decrement'];
                        $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised_total'] ??
                            $finalAccount['compromised'];
                        $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['compromised_caused'] ??
                            $finalAccount['caused'];
                        $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['compromised_paid'] ??
                            $finalAccount['paid'];
                        $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'] ??
                            $finalAccount['accountOpens'][0]['created_at'] ??
                            $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'];

                        if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                            foreach ($finalAccount['modifications'] as $mod) {
                                $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment_total'] ??
                                    $mod['increment'];
                                $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement_total'] ??
                                    $mod['decrement'];
                                $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised_total'] ??
                                    $mod['compromised'];
                                $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['compromised_caused'] ??
                                    $mod['caused'];
                                $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['compromised_paid'] ??
                                    $mod['paid'];
                                $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                            }
                        }
                    } elseif ($finalAccount->subspecific == 00) {
                        $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment_total'] ??
                            $finalAccount['increment'];
                        $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement_total'] ??
                            $finalAccount['decrement'];
                        $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised_total'] ??
                            $finalAccount['compromised'];
                        $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['compromised_caused'] ??
                            $finalAccount['caused'];
                        $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['compromised_paid'] ??
                            $finalAccount['paid'];
                        $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'];

                        if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                            foreach ($finalAccount['modifications'] as $mod) {
                                $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment_total'] ??
                                    $mod['increment'];
                                $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement_total'] ??
                                    $mod['decrement'];
                                $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised_total'] ??
                                    $mod['compromised'];
                                $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['compromised_caused'] ??
                                    $mod['caused'];
                                $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['compromised_paid'] ??
                                    $mod['paid'];
                                $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                            }
                        }
                    } elseif ($finalAccount->subspecific != 00) {
                        $finalAccounts[$finalAccountParent->code]['increment_total'] += $finalAccount['increment'];
                        $finalAccounts[$finalAccountParent->code]['decrement_total'] += $finalAccount['decrement'];
                        $finalAccounts[$finalAccountParent->code]['compromised_total'] += $finalAccount['compromised'];
                        $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $finalAccount['caused'];
                        $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $finalAccount['paid'];
                        $finalAccounts[$finalAccountParent->code]['date'] = $finalAccount['date'] ??
                            $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'];

                        if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                            foreach ($finalAccount['modifications'] as $mod) {
                                $finalAccounts[$finalAccountParent->code]['increment_total'] += $mod['increment'];
                                $finalAccounts[$finalAccountParent->code]['decrement_total'] += $mod['decrement'];
                                $finalAccounts[$finalAccountParent->code]['compromised_total'] += $mod['compromised'];
                                $finalAccounts[$finalAccountParent->code]['compromised_caused'] += $mod['caused'];
                                $finalAccounts[$finalAccountParent->code]['compromised_paid'] += $mod['paid'];
                                $finalAccounts[$finalAccountParent->code]['date'] = $mod['date'];
                            }
                        }
                    }
                }

                if (!isset($finalAccount['increment_total'])) {
                    $finalAccount['increment_total'] = $finalAccount['increment'];
                }

                if (!isset($finalAccount['decrement_total'])) {
                    $finalAccount['decrement_total'] = $finalAccount['decrement'];
                }

                if (!isset($finalAccount['compromised_total'])) {
                    $finalAccount['compromised_total'] = $finalAccount['compromised'];
                }

                if (!isset($finalAccount['compromised_caused'])) {
                    $finalAccount['compromised_caused'] = $finalAccount['caused'];
                }

                if (!isset($finalAccount['compromised_paid'])) {
                    $finalAccount['compromised_paid'] = $finalAccount['paid'];
                }

                if (!isset($finalAccount['date'])) {
                    $finalAccount['date'] = $finalAccount['accountOpens'][0]['created_at'] ??
                        $finalAccount['modificationAccounts'][0]['budgetSubSpecificFormulation']['created_at'] ??
                        $finalAccount['modificationAccounts'][0]['created_at'];
                }

                $finalAccount['current'] = $finalAccount['self_amount'] + $finalAccount['increment_total'] -
                    $finalAccount['decrement_total'];

                $finalAccount['self_available'] = $finalAccount['current'] - $finalAccount['compromised_total'];

                if (isset($finalAccount['modifications']) && count($finalAccount['modifications']) > 0) {
                    $accountCurrent = $finalAccount['current'];
                    $available = $finalAccount['current'];

                    $finalAccount['modifications'] = collect($finalAccount['modifications'])->map(function ($mod) use (
                        &$accountCurrent,
                        &$available
                    ) {
                        if ($mod['increment'] > 0) {
                            $accountCurrent = $accountCurrent + $mod['current'];
                            $available = $available + $mod['current'];
                        } elseif ($mod['decrement'] > 0) {
                            $accountCurrent = $accountCurrent - $mod['current'];
                            $available = $available - $mod['current'];
                        } elseif ($mod['compromised'] > 0) {
                            $available = $available - $mod['current'];
                        } elseif ($mod['compromised'] < 0) {
                            $available = $available - $mod['current'];
                        }

                        $mod['current'] = $accountCurrent;
                        $mod['self_available'] = $available;

                        $previousCurrent = $available;

                        return $mod;
                    })->all();
                }
            }

            ksort($finalAccounts);

            array_push(
                $project_accounts_open,
                [
                    $finalAccounts,
                    $specificAction->subSpecificFormulations[0],
                    "project_code" => $project->code,
                    "specific_action_code" => $specificAction->code,
                    "specific_action_name" => $specificAction->name,
                ]
            );
        }

        foreach ($project_accounts_open as $accounts) {
            array_filter($accounts[0], function ($account) use ($accountsWithMovements) {
                if ($accountsWithMovements && ($account['amount_available'] === $account['total_year_amount'])) {
                    return false;
                }

                return true;
            });
        }

        return $project_accounts_open;
    }

    /**
     * Metodo que retorna el monto comprometido para la cuenta $account_id
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param integer   $account_id     Datos de la cuenta presupuestaria
     * @param string    $initialDate    Fecha inicial de consulta
     * @param string    $finalDate      Fecha final de consulta
     *
     * @return array Monto del compromiso para la cuenta presupuestaria con 'id' $account_id     *
     */
    public function getAccountCompromisedAmout(object $accout_id, $initialDate, $finalDate)
    {
        $compromised = BudgetCompromiseDetail::query()
            ->with(['budgetCompromise' => function ($query) use ($initialDate, $finalDate) {
                $query->whereBetween('compromised_at', [$initialDate, $finalDate]);
            }])
            ->where('budget_sub_specific_formulation_id', $accout_id->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $accout_id->budget_account_id)
            ->get();

        $compromises = [];

        $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

        if (!$compromised->isEmpty()) {
            foreach ($compromised as $com) {
                if ($com->budgetCompromise) {
                    $budgetCompromiseId = $com->budgetCompromise->id;

                    // Iteración con monto en positivo
                    $positiveKey = $budgetCompromiseId;
                    if (!array_key_exists($positiveKey, $compromises)) {
                        $compromises[$positiveKey] = [
                            'amount' => 0,
                            'description' => '',
                            'date' => '',
                        ];
                    }

                    $compromises[$positiveKey]['amount'] += $com->amount;
                    $compromises[$positiveKey]['description'] = $com->budgetCompromise->description;

                    if (gettype($com->budgetCompromise->compromised_at) === 'string') {
                        $compromises[$positiveKey]['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $com->budgetCompromise->compromised_at);
                    } else {
                        $compromises[$positiveKey]['date'] = $com->budgetCompromise->compromised_at;
                    }

                    // Iteración con monto en negativo
                    if ($anulatedStatus->id == $com->document_status_id) {
                        $negativeKey = $budgetCompromiseId . '_negative';
                        if (!array_key_exists($negativeKey, $compromises)) {
                            $compromises[$negativeKey] = [
                                'amount' => 0,
                                'description' => '',
                                'date' => '',
                            ];
                        }

                        $compromises[$negativeKey]['amount'] += $com->amount * -1;
                        $compromises[$negativeKey]['description'] = $com->budgetCompromise->description;

                        if (gettype($com->budgetCompromise->compromised_at) === 'string') {
                            $compromises[$negativeKey]['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $com->budgetCompromise->compromised_at);
                        } else {
                            $compromises[$negativeKey]['date'] = $com->budgetCompromise->compromised_at;
                        }
                    }
                }
            }

            return $compromises;
        }
        return $compromises;
    }

    /**
     * Metodo que retorna las modificaciones de una cuenta
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param int $account_budget_sub_specific_formulation_id Identificador de la cuenta presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null Objecto que contiene aumentos y
     *                                                                                               disminuciones
     */
    public function getAccountModifications(int $account_budget_sub_specific_formulation_id)
    {
        $modifications = BudgetModificationAccount::with('budgetModification')->where('budget_sub_specific_formulation_id', $account_budget_sub_specific_formulation_id)->get();
        return !$modifications->isEmpty() ? $modifications : null;
    }

    /**
     * Metodo para filtrar y retornar un array con las cuentas presupuestarias formuladas
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param array     $budgetAccountsOpen Arreglo de cuentas presupuestarias abiertas
     * @param integer   $initialCode Código inicial de la Cuenta presupuestaria
     * @param integer   $finalCode Código final de la Cuenta presupuestaria
     * @param string    $initialDate Fecha inicial de la Cuenta presupuestaria
     * @param string    $finalDate Fecha final de la Cuenta presupuestaria
     *
     * @return    array Arreglo ordenado de cuentas presupuestarias formuladas
     */
    public function filterBudgetAccounts(
        array $budgetAccountsOpen,
        int $initialCode,
        int $finalCode,
        string $initialDate,
        string $finalDate
    ) {
        $filteredArray = array();

        foreach ($budgetAccountsOpen as $budgetItem) {
            if ($budgetItem->code > $finalCode) {
                break;
            }

            if (isset($budgetItem->budgetAccount)) {
                $code = str_replace('.', '', $budgetItem->budgetAccount->getCodeAttribute());
            } else {
                $code = str_replace('.', '', $budgetItem->getCodeAttribute());
            }

            if ($code >= $initialCode && $code <= $finalCode) {
                array_push($filteredArray, $budgetItem);
            }
        }

        return $filteredArray;
    }

    /**
     * Metodo para generar el reporte en PDF de disponibilad presupuestaria
     *
     * @author    Jonathan Alvarado <wizardx1407@gmail.com> | <jonathanalvarado1407@gmail.com>     *
     * @author  José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */
    public function getPdf(Request $request)
    {
        $data = $request->validate([
            'initialDate' => ['required', 'before_or_equal:finalDate'],
            'finalDate' => ['required', 'after_or_equal:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
            'project_id' => 'required',
            'project_type' => 'required',
            'specific_actions_ids' => 'required',
        ]);

        // Convierte un string que contiene enteros en un array de enteros
        $data["specific_actions_ids"] = json_decode('[' . $request->all()["specific_actions_ids"] . ']', true);
        $ids = $data["specific_actions_ids"];

        $pdf = new ReportRepository();

        if ($request->project_type === 'project') {
            $project = BudgetProject::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        } else {
            $project = BudgetCentralizedAction::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        }

        $records = $this->getBudgetAccountsOpen($data['accountsWithMovements'], $project, $data['initialDate'], $data['finalDate']);

        for ($i = 0; $i < count($records); $i++) {
            $records[$i][0] = $this->filterBudgetAccounts($records[$i][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
        }

        $institution = Institution::find(1);
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', auth()->user()->id)->first();
        $date = 'Información Presupuestaria desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['initialDate'])->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['finalDate'])->format('d-m-Y');

        $pdf->setConfig(['institution' => $institution, 'orientation' => 'P', 'reportDate' => '', 'urlVerify'   => url('')]);
        $pdf->setHeader('Reporte de Disponibilidad Presupuestaria', $date);
        $pdf->setFooter();
        $pdf->setBody('budget::pdf.budgetAvailability', true, [
            'pdf' => $pdf,
            'records' => $records,
            'institution' => $institution,
            'currencySymbol' => $currency['symbol'],
            'fiscal_year' => $fiscal_year['year'],
            "report_date" => \Carbon\Carbon::today()->format('d-m-Y'),
            'profile' => $profile,
        ]);
    }

    /**
     * Metodo que retorna el monto comprometido para la cuenta $account_id
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */

    public function consolidatedReportPdf(Request $request)
    {

        $request->validate([
            'initialDate' => ['required', 'before_or_equal:finalDate'],
            'finalDate' => ['required', 'after_or_equal:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
        ]);

        $data = $request->toArray();
        $data["projects_ids"] = json_decode('[' . $data["projects_ids"] . ']', true);
        $data["centralized_actions_ids"] = json_decode('[' . $data["centralized_actions_ids"] . ']', true);

        $projects = BudgetProject::with(['specificActions' => function ($query) {
            $query->with(['subSpecificFormulations' => function ($query) {
                $query->with(['accountOpens' => function ($query) {
                    $query->with('budgetAccount');
                    $query->orderBy('id', 'desc');
                }])->whereHas('accountOpens');
            }])->whereHas('subSpecificFormulations');
        }])->whereIn('id', $data["projects_ids"])->get();

        $centrilized_actions = BudgetCentralizedAction::with(['specificActions' => function ($query) {
            $query->with(['subSpecificFormulations' => function ($query) {
                $query->with(['accountOpens' => function ($query) {
                    $query->with('budgetAccount');
                    $query->orderBy('id', 'desc');
                }])->whereHas('accountOpens');
            }])->whereHas('subSpecificFormulations');
        }])->whereIn('id', $data["centralized_actions_ids"])->get();

        $projects_accounts = array();
        foreach ($projects as $project) {
            array_push($projects_accounts, $this->getBudgetAccountsOpen($data['accountsWithMovements'], $project, $data['initialDate'], $data['finalDate']));
        }

        $centrilized_actions_accounts = array();
        foreach ($centrilized_actions as $centrilized_action) {
            array_push($centrilized_actions_accounts, $this->getBudgetAccountsOpen($data['accountsWithMovements'], $centrilized_action, $data['initialDate'], $data['finalDate']));
        }

        $records = array();

        foreach ($projects_accounts as $projects_account) {
            $projects_account[0][0] = $this->filterBudgetAccounts($projects_account[0][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
            array_push($records, ...$projects_account);
        }

        foreach ($centrilized_actions_accounts as $centrilized_actions_account) {
            $centrilized_actions_account[0][0] = $this->filterBudgetAccounts($centrilized_actions_account[0][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
            array_push($records, ...$centrilized_actions_account);
        }

        $pdf = new ReportRepository();

        $institution = Institution::find(1);
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', auth()->user()->id)->first();
        $date = 'Proyectos y Acciones Centralizadas desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['initialDate'])->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['finalDate'])->format('d-m-Y');

        $pdf->setConfig(['institution' => $institution, 'orientation' => 'P', 'reportDate' => '', 'urlVerify'   => url('')]);
        $pdf->setHeader('Reporte de Presupuesto', $date);
        $pdf->setFooter();
        $pdf->setFooter();
        $pdf->setBody('budget::pdf.budgetAvailability', true, [
            'pdf' => $pdf,
            'records' => $records,
            'institution' => $institution,
            'currencySymbol' => $currency['symbol'],
            'fiscal_year' => $fiscal_year['year'],
            "report_date" => \Carbon\Carbon::today()->format('d-m-Y'),
            'profile' => $profile,
        ]);
    }

    /**
     * Muestra el formulario para generar el reporte de proyectos
     *
     * @return \Illuminate\View\View
     */
    public function getProjectsView()
    {
        return view('budget::reports.projects');
    }

    /**
     * Obtiene los datos para generar el reporte de proyectos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getProjectsReportData(Request $request)
    {
        try {
            $project_code = $request->input('project_code');
            $search = $request->input('search');

            $query = BudgetProject::query();

            if ($project_code) {
                $query->where("code", "LIKE", "%" . $project_code . "%");
            }

            if ($search) {
                $query->where("name", "LIKE", "%" . $search . "%");
            }

            $query = $query->get();

            $response = [
                'data' => $query,
                "message" => "Data para reporte de proyectos",
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener la data para el reporte de proyectos";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];
        }

        return response()->json($response, $code ?? 200);
    }

    /**
     * Genera el reporte de proyectos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getProjectsReportPdf(Request $request)
    {
        try {
            $project_code = $request->input('project_code');
            $search = $request->input('search');

            $query = BudgetProject::query();

            if ($project_code) {
                $query->where("code", "LIKE", "%" . $project_code . "%");
            }

            if ($search) {
                $query->where("name", "LIKE", "%" . $search . "%");
            }

            $query = $query->get();

            $pdf = new ReportRepository();
            $institution = Institution::find(1);

            $pdf->setConfig(['institution' => $institution]);
            $pdf->setHeader('Reporte de proyectos', 'Reporte de proyectos de la institucion');
            $pdf->setFooter();
            $pdf->setBody('budget::pdf.projects', true, [
                'pdf' => $pdf,
                'records' => $query,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener la data para el reporte de proyectos";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];

            return response()->json($response, $code ?? 200);
        }
    }

    /**
     * Método que recopila todos los años que poseen formulaciones
     *
     * @return \Illuminate\View\View
     */
    public function getFormulatedView()
    {
        $formulations_years = BudgetSubSpecificFormulation::select(['year'])->distinct()->get()->all();
        $years = json_encode(array_column($formulations_years, 'year'));
        $budgetProjects = $this->getBudgetProjects(true);
        $budgetCentralizedActions = $this->getBudgetCentralizedActions(true);

        return view('budget::reports.budgetFormulated', [
            'years' => $years,
            'budgetProjects' => json_encode($budgetProjects),
            'budgetCentralizedActions' => json_encode($budgetCentralizedActions)
        ]);
    }

    /**
     * Obtiene los datos de las formulaciones
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getFormulations(Request $request)
    {
        $entity = $request->input('is_project')
        ? BudgetProject::class
        : BudgetCentralizedAction::class;

        $id = $request->input('id');

        $query = BudgetSubSpecificFormulation::query();

        $query = $query->whereHas('specificAction', function ($query) use ($entity, $id) {
            $query->whereHasMorph('specificable', [BudgetProject::class, BudgetCentralizedAction::class], function ($query) use ($entity, $id) {
                return $query->where('specificable_id', $id)
                    ->where('specificable_type', $entity);
            });
        });

        $query = $query->get();

        $formulations = $query->count() ? [['id' => '', 'text' => 'Seleccione']] : [];

        foreach ($query as $formulation) {
            $formulations[] = [
                'id' => $formulation->id,
                'text' => $formulation->specificAction->code . ' - ' . $formulation->specificAction->name,
            ];
        }

        return response()->json($formulations);
    }

    /**
     * Obtiene los datos para el reporte de presupuesto formulado
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getFormulatedReportData(Request $request)
    {
        $formulation_id = $request->input('formulation_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation_id);

        if ($start_date) {
            $query->where('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $query->where('created_at', '<=', $end_date);
        }

        $query = $query->get();

        $total = $query->sum('total_real_amount');

        foreach ($query as $account) {
            $account->code = $account->budgetAccount->getCodeAttribute();
            $account->percentage = round(($account->total_real_amount * 100) / $total);
            $account->total = $total;
        }

        return response()->json(['data' => $query]);
    }

    /**
     * Genera el reporte de presupuesto formulado
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getFormulatedReportPdf(Request $request)
    {
        $request->validate([
            'formulation_id' => $request->input('all_specific_actions') === 'true' ?
                'nullable' :
                ['required', 'array', 'not_in:0|null, ""'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
        ], [], [
            'formulation_id' => 'El campo Acción Especifica',
            'start_date' => 'El campo Desde',
            'end_date' => 'El campo Hasta',
        ]);

        try {
            $formulation_id = explode(',', $request->input('formulation_id')[0]);
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $isProject = BudgetProject::class;
            $isCentralizedAction = BudgetCentralizedAction::class;

            if ($request->all_specific_actions == 'true') {
                if ($request->is_project) {
                    $formulation = BudgetSubSpecificFormulation::query()->whereHas('specificAction', function ($query) use ($isProject) {
                        $query->whereHasMorph('specificable', [BudgetProject::class], function ($query) use ($isProject) {
                            return $query->where('specificable_type', $isProject)->where('specificable_id', 1);
                        });
                    })->get();
                } else {
                    $formulation = BudgetSubSpecificFormulation::query()->whereHas('specificAction', function ($query) use ($isCentralizedAction) {
                        $query->whereHasMorph('specificable', [BudgetCentralizedAction::class], function ($query) use ($isCentralizedAction) {
                            return $query->where('specificable_type', $isCentralizedAction)->where('specificable_id', 1);
                        });
                    })->get();
                }
            } else {
                $formulation = BudgetSubSpecificFormulation::query()->whereIn('id', $formulation_id)->get();
            }

            $pdf = new ReportRepository();

            $institution = Institution::find(1);

            $fiscal_year = FiscalYear::where('active', true)->first();

            $currency = Currency::where('default', true)->first();

            $profile = Profile::where('user_id', auth()->user()->id)->first();

            $date = 'Presupuesto Formulado desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $start_date)->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $end_date)->format('d-m-Y');

            $totalFormulations = 0;

            foreach ($formulation as $form) {
                $totalFormulations += $form->total_formulated;
            }

            $pdf->setConfig([
                'institution' => $institution,
                'orientation' => 'L',
                'format' => 'A2 LANDSCAPE',
                'urlVerify'   => url(''),
            ]);

            $pdf->setHeader(
                "Reporte de Presupuesto Formulado",
                $date,
            );

            $pdf->setFooter();

            $pdf->setBody('budget::pdf.formulations', true, [
                'pdf' => $pdf,
                'formulations' => $formulation,
                'totalFormulations' => $totalFormulations,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $code = $e->getCode() ? (is_numeric($e->getCode()) ? $e->getCode() : 500) : 500;
            $msg = $e->getMessage() ?? "Error al obtener los datos para el reporte de presupuestos formulados";
            $response = [
                "message" => $msg,
                "errors" => [],
            ];

            return response()->json($response, $code ?? 200);
        }
    }

    /**
     * Obtiene información delos proyectos
     *
     * @param bool $list Indica si debe retornar una lista
     *
     * @return array
     */
    public function getBudgetProjects(bool $list = null)
    {
        $budgetProjects = BudgetProject::with(['specificActions'])->whereHas('specificActions', function ($query) {
            $query->whereHas('subSpecificFormulations', function ($query) {
                $query->where('assigned', true);
            });
        })->get()->all();

        if ($list) {
            $budgetProjects = array_map(function ($budgeProject) {
                return array(
                    'id' => $budgeProject->id,
                    'text' => $budgeProject->code . ' - ' . $budgeProject->name,
                );
            }, $budgetProjects);

            array_unshift($budgetProjects, ['id' => "", 'text' => "Seleccione..."]);
            return $budgetProjects;
        }

        return $budgetProjects;
    }

    /**
     * Obtiene información de las acciones centralizadas
     *
     * @param bool $list Indica si debe retornar una lista
     *
     * @return array
     */
    public function getBudgetCentralizedActions(bool $list = null)
    {
        $budgetCentralizedActions = BudgetCentralizedAction::with(['specificActions'])->whereHas('specificActions', function ($query) {
            $query->whereHas('subSpecificFormulations', function ($query) {
                $query->where('assigned', true);
            });
        })->get()->all();

        if ($list) {
            $budgetCentralizedActions = array_map(function ($budgetCentralizedAction) {
                return array(
                    'id' => $budgetCentralizedAction->id,
                    'text' => $budgetCentralizedAction->code . ' - ' . $budgetCentralizedAction->name,
                );
            }, $budgetCentralizedActions);

            array_unshift($budgetCentralizedActions, ['id' => "", 'text' => "Seleccione..."]);

            return $budgetCentralizedActions;
        }

        return $budgetCentralizedActions;
    }

    /**
     * Muestra el formulario para la generación del reporte del mayor analítico
     *
     * @return \Illuminate\View\View
     */
    public function budgetAnalyticalMajor()
    {
        $budgetAvailability = $this->budgetAvailability(true);
        return view('budget::reports.budgetAnalyticalMajor', $budgetAvailability);
    }

    /**
     * Genera el reporte de mayor analítico
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|void
     */
    public function getbudgetAnalyticalMajorPdf(Request $request)
    {
        $data = $request->validate([
            'initialDate' => ['required', 'before:finalDate'],
            'finalDate' => ['required', 'after:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
        ]);

        $data = $request->toArray();
        $data["specific_actions_ids"] = json_decode('[' . $data["specific_actions_ids"] . ']', true);
        $ids = $data["specific_actions_ids"];

        if ($request->project_type === 'project') {
            $project = BudgetProject::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        } else {
            $project = BudgetCentralizedAction::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($data["project_id"]);
        }

        $records = $this->getBudgetAccountsOpen($data['accountsWithMovements'], $project, $data['initialDate'], $data['finalDate']);

        for ($i = 0; $i < count($records); $i++) {
            $records[$i][0] = $this->filterBudgetAccounts($records[$i][0], $data['initialCode'], $data['finalCode'], $data['initialDate'], $data['finalDate']);
        }

        $institution = Institution::find(1);
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', auth()->user()->id)->first();
        $date = 'Mayor Analítico desde ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['initialDate'])->format('d-m-Y') . ' hasta ' . \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $data['finalDate'])->format('d-m-Y');

        if ($request->exportReport === 'true') {
            return Excel::download(new RecordsExport([
                'records' => $records, 'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'report_date' => \Carbon\Carbon::today()->format('d-m-Y'),
                'initialDate' => '',
                'finalDate' => '',
                'report_type_id' => $data['report_type_id'],
                'profile' => $profile,
            ]), now()->format('d-m-Y') . '_Reporte_Mayor_Analitico.csv');
        } else {
            $pdf = new ReportRepository();
            $pdf->setConfig(['institution' => $institution, 'orientation' => 'L', 'format' => 'A2 LANDSCAPE', 'urlVerify' => url('')]);
            $pdf->setHeader('Reporte Mayor Analítico por Proyecto o Acción Centralizada', $date);
            $pdf->setFooter();
            $pdf->setBody('budget::pdf.budgetAnalyticMajor', true, [
                'pdf' => $pdf,
                'records' => $records,
                'institution' => $institution,
                'currencySymbol' => $currency['symbol'],
                'fiscal_year' => $fiscal_year['year'],
                'report_date' => \Carbon\Carbon::today()->format('d-m-Y'),
                'initialDate' => \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $request['initialDate'])->format('d-m-Y'),
                'finalDate' => \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $request['finalDate'])->format('d-m-Y'),
                'profile' => $profile,
            ]);
        }
    }

    /**
     * Crea el reporte de mayor analítico
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBudgetAnalyticalMajorPdf(Request $request)
    {
        $userId = auth()->user()->id;
        $data = $request->validate([
            'initialDate' => ['required', 'before:finalDate'],
            'finalDate' => ['required', 'after:initialDate'],
            'initialCode' => 'required',
            'finalCode' => 'required',
            'accountsWithMovements' => 'required',
        ]);

        $data = $request->toArray();
        $created_at = \Carbon\Carbon::now();
        CreateBudgetAnalyticalMajorJob::dispatch(
            $data,
            'budget::pdf.budgetAnalyticMajor',
            'report-budget-analytic-major',
            $userId,
            $created_at
        );
        return response()->json(['result' => true], 200);
    }

    /**
     * Método para buscar las cuentas padre de una formulación
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array con las cuentas padre de la formulación
     */
    public function getAccountParents($child, $parents = [])
    {
        if (!isset($child)) {
            return $parents;
        }

        if (!array_key_exists($child->id, $parents)) {
            $parents[$child->id] = $child;
        }

        if ($child->parent_id == null) {
            return $parents;
        } else {
            $child->load('accountParent');
            $parent = $child->accountParent;
            $parent['increment'] += $child['increment'];

            return $this->getAccountParents($parent, $parents);
        }
    }

    /**
     * Metodo que retorna el monto causado para la cuenta $account
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param BudgetAccount $account Cuenta presupuestaria
     * @param string $initialDate Fecha inicial
     * @param string $finalDate   Fecha final
     *
     * @return float|array Monto del causado para la cuenta presupuestaria $account     *
     */

    public function getAccountCompromisedCausedAmount($account, $initialDate, $finalDate)
    {
        $compromised = BudgetCompromiseDetail::query()
            ->with(['budgetCompromise' => function ($query) use ($initialDate, $finalDate) {
                $query
                    ->whereBetween('compromised_at', [$initialDate, $finalDate])
                    ->with(['budgetStages' => function ($q) {
                        $q->withTrashed()->with('stageable')->where('type', 'CAU');
                    }]);
            }])
            ->where('budget_sub_specific_formulation_id', $account->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $account->budget_account_id)
            ->get();

        $compromises = [];
        $amount = [
            'amount' => 0,
            'date' => '',
        ];

        $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

        if (count($compromised) > 0) {
            if (isset($compromised[0]) && isset($compromised[0]['budgetCompromise'])) {
                foreach ($compromised as $com) {
                    if ($com->budgetCompromise) {
                        $budgetCompromiseId = $com->budgetCompromise->id;

                        // Iteración con monto en positivo
                        $positiveKey = $budgetCompromiseId;

                        // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                        if (!array_key_exists($positiveKey, $compromises)) {
                            $amount = [
                                'amount' => 0,
                                'date' => ''
                            ];
                        } else {
                            // Si ya existe, obtenemos su valor actual
                            $amount = $compromises[$positiveKey];
                        }

                        if ($com->budgetCompromise->budgetStages) {
                            $stageCau = false;
                            foreach ($com->budgetCompromise->budgetStages as $stage) {
                                if ($stage->type == 'CAU') {
                                    $stageCau = true;
                                }
                                $date = $stage->stageable->ordered_at ?? $stage->stageable->payment_date;
                                // Seteamos la fecha desde el último elemento de budgetStages
                                if (gettype($date) === 'string') {
                                    $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                } else {
                                    $amount['date'] = $date;
                                }
                            }

                            $newAmount = $com->amount;

                            if ($stageCau) {
                                $amount['amount'] += $newAmount;
                                $compromises[$positiveKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                            }
                        }

                        if ($anulatedStatus->id == $com->document_status_id) {
                            // Iteración con monto en negativo
                            $negativeKey = $budgetCompromiseId . '_negative';

                            // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                            if (!array_key_exists($negativeKey, $compromises)) {
                                $amount = [
                                    'amount' => 0,
                                    'date' => ''
                                ];
                            } else {
                                // Si ya existe, obtenemos su valor actual
                                $amount = $compromises[$negativeKey];
                            }

                            if ($com->budgetCompromise->budgetStages) {
                                $stageCau = false;
                                foreach ($com->budgetCompromise->budgetStages as $stage) {
                                    if ($stage->type == 'CAU') {
                                        $stageCau = true;
                                    }
                                    $date = $stage->stageable->ordered_at ?? $stage->stageable->payment_date;
                                    // Seteamos la fecha desde el último elemento de budgetStages
                                    if (gettype($date) === 'string') {
                                        $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                    } else {
                                        $amount['date'] = $date;
                                    }
                                }

                                $newAmount = $com->amount * -1;

                                if ($stageCau) {
                                    $amount['amount'] += $newAmount;
                                    $compromises[$negativeKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                                }
                            }
                        }
                    }
                }

                return $compromises;
            }
        }

        return $compromises;
    }

    /**
     * Metodo que retorna el monto pagado para la cuenta $account
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param BudgetAccount $account Cuenta presupuestaria
     * @param string $initialDate Fecha inicial
     * @param string $finalDate   Fecha final
     *
     * @return array Monto del pagado para la cuenta presupuestaria $account     *
     */

    public function getAccountCompromisedPaidAmount($account, $initialDate, $finalDate)
    {
        $compromised = BudgetCompromiseDetail::query()
            ->with(['budgetCompromise' => function ($query) use ($initialDate, $finalDate) {
                $query
                    ->whereBetween('compromised_at', [$initialDate, $finalDate])
                    ->with(['budgetStages' => function ($q) {
                        $q->withTrashed()->with('stageable')->where('type', 'PAG');
                    }]);
            }])
            ->where('budget_sub_specific_formulation_id', $account->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $account->budget_account_id)
            ->get();

        $anulatedStatus = DocumentStatus::where('action', 'AN')->first();

        $compromises = [];
        $amount = [
            'amount' => 0,
            'date' => '',
        ];

        if (count($compromised) > 0) {
            if (isset($compromised[0]) && isset($compromised[0]['budgetCompromise'])) {
                foreach ($compromised as $com) {
                    if ($com->budgetCompromise) {
                        $budgetCompromiseId = $com->budgetCompromise->id;

                        // Iteración con monto en positivo
                        $positiveKey = $budgetCompromiseId;

                        // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                        if (!array_key_exists($positiveKey, $compromises)) {
                            $amount = [
                                'amount' => 0,
                                'date' => ''
                            ];
                        } else {
                            // Si ya existe, obtenemos su valor actual
                            $amount = $compromises[$positiveKey];
                        }

                        if ($com->budgetCompromise->budgetStages) {
                            $stagePag = false;
                            foreach ($com->budgetCompromise->budgetStages as $stage) {
                                if ($stage->type == 'PAG') {
                                    $stagePag = true;
                                }
                                $date = $stage->stageable->paid_at ?? $stage->stageable->payment_date;
                                // Seteamos la fecha desde el último elemento de budgetStages
                                if (gettype($date) === 'string') {
                                    $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                } else {
                                    $amount['date'] = $date;
                                }
                            }

                            $newAmount = $com->amount;

                            if ($stagePag) {
                                $amount['amount'] += $newAmount;
                                $compromises[$positiveKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                            }
                        }

                        if ($anulatedStatus->id == $com->document_status_id) {
                            // Iteración con monto en negativo
                            $negativeKey = $budgetCompromiseId . '_negative';

                            // Verificamos si el identificador existe en $compromises y lo inicializamos si no
                            if (!array_key_exists($negativeKey, $compromises)) {
                                $amount = [
                                    'amount' => 0,
                                    'date' => ''
                                ];
                            } else {
                                // Si ya existe, obtenemos su valor actual
                                $amount = $compromises[$negativeKey];
                            }

                            if ($com->budgetCompromise->budgetStages) {
                                $stagePag = false;
                                foreach ($com->budgetCompromise->budgetStages as $stage) {
                                    if ($stage->type == 'PAG') {
                                        $stagePag = true;
                                    }
                                    $date = $stage->stageable->paid_at ?? $stage->stageable->payment_date;
                                    // Seteamos la fecha desde el último elemento de budgetStages
                                    if (gettype($date) === 'string') {
                                        $amount['date'] = \Carbon\Carbon::rawCreateFromFormat('Y-m-d', $date);
                                    } else {
                                        $amount['date'] = $date;
                                    }
                                }

                                $newAmount = $com->amount * -1;

                                if ($stagePag) {
                                    $amount['amount'] += $newAmount;
                                    $compromises[$negativeKey] = $amount; // Actualizamos el valor en el arreglo $compromises
                                }
                            }
                        }
                    }
                }

                return $compromises;
            }
        }

        return $compromises;
    }
}
