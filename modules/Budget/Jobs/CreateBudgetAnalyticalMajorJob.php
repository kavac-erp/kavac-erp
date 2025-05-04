<?php

namespace Modules\Budget\Jobs;

use App\Models\FiscalYear;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Modules\Budget\Mail\BudgetSendMail;
use Modules\Budget\Models\BudgetAccount;
use Modules\Budget\Models\BudgetCentralizedAction;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Modules\Budget\Models\BudgetProject;
use Modules\Budget\Models\Currency;
use Modules\Budget\Models\DocumentStatus;
use Modules\Budget\Models\Institution;

/**
 * @class CreateBudgetAnalyticalMajorJobs
 * @brief Clase que gestiona las tareas en segundo plano para generar el reporte del mayor analítico del módulo de presupuesto
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateBudgetAnalyticalMajorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Objeto que contiene la información asociada a la solicitud
     *
     * @var object $asset
     */
    protected $data;

    /**
     * Plantilla o texto a incluir en el cuerpo del reporte
     *
     * @var string $body
     */
    protected $body;

    /**
     * Objeto que contiene el código interno asociada a la solicitud
     *
     * @var object $code
     */
    protected $code;

    /**
     * Título del reporte
     *
     * @var string $title
     */
    protected $title;

    /**
     * Subtítulo o descripción del reporte
     *
     * @var string $subtitle
     */
    protected $subtitle;

    /**
     * Operación a realizar al finalizar el trabajo
     *
     * @var string $operation
     */
    protected $operation;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia del trabajo
     *
     * @param  array $data Arreglo con la información a procesar
     * @param  string $body Cuerpo del reporte
     * @param  string $title Título del reporte
     * @param  integer $userId Identificador del usuario que genera el reporte
     * @param  string $created_at Fecha de creación del reporte
     *
     * @return void
     */
    public function __construct(array $data, string $body, string $title = null, protected ?int $userId = null, protected string $created_at = '')
    {
        $this->data = $data;
        $this->body = $body;
        $this->title = $title;
    }

    /**
     * Ejecuta el trabajo
     *
     * @return void
     */
    public function handle()
    {
        $ids = $this->data["specific_actions_ids"];

        if ($this->data["project_type"] === 'project') {
            $project = BudgetProject::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($this->data["project_id"]);
        } else {
            $project = BudgetCentralizedAction::with(['specificActions' => function ($query) use ($ids) {
                $query->with(['subSpecificFormulations' => function ($query) {
                    $query->with(['accountOpens' => function ($query) {
                        $query->with('budgetAccount');
                        $query->orderBy('id', 'desc');
                    }])->whereHas('accountOpens');
                }])->whereIn('id', $ids)->get();
            }])->find($this->data["project_id"]);
        }

        $records = $this->getBudgetAccountsOpen(
            $this->data['accountsWithMovements'],
            $project,
            $this->data['initialDate'],
            $this->data['finalDate']
        );

        for ($i = 0; $i < count($records); $i++) {
            $records[$i][0] = $this->filterBudgetAccounts(
                $records[$i][0],
                $this->data['initialCode'],
                $this->data['finalCode'],
                $this->data['initialDate'],
                $this->data['finalDate']
            );
        }

        $institution = Institution::where('default', true)->where('active', true)->first();
        $fiscal_year = FiscalYear::where('active', true)->first();
        $currency = Currency::where('default', true)->first();
        $profile = Profile::where('user_id', $this->userId)->first();
        $date = 'Mayor Analítico desde ' .
                Carbon::rawCreateFromFormat('Y-m-d', $this->data['initialDate'])->format('d-m-Y') .
                ' hasta ' .
                Carbon::rawCreateFromFormat('Y-m-d', $this->data['finalDate'])->format('d-m-Y');

        $pdf = new ReportRepository();
        $pdfPath = storage_path() . '/reports/budeget-report-' . $this->created_at . '.pdf';

        $pdf->setConfig([
            'institution' => $institution,
            'orientation' => 'L',
            'format' => 'A2 LANDSCAPE',
            'urlVerify' => url(''),
            'filename' => $pdfPath,
        ]);
        $pdf->setHeader('Reporte Mayor Analítico por Proyecto o Acción Centralizada', $date);
        $pdf->setFooter();
        $pdf->setBody('budget::pdf.budgetAnalyticMajor', true, [
            'pdf' => $pdf,
            'records' => $records,
            'institution' => $institution,
            'currencySymbol' => $currency['symbol'],
            'fiscal_year' => $fiscal_year['year'],
            'report_date' => Carbon::today()->format('d-m-Y'),
            'initialDate' => Carbon::rawCreateFromFormat('Y-m-d', $this->data['initialDate'])->format('d-m-Y'),
            'finalDate' => Carbon::rawCreateFromFormat('Y-m-d', $this->data['finalDate'])->format('d-m-Y'),
            'profile' => $profile,
        ], 'F');

        $user = User::find($this->userId);

        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha generado el reporte de mayor analítico, '
                    . 'el archivo ha sido enviado a su correo electrónico',
                )
            );

            $email = $user->email;
        }

        $mailable = new BudgetSendMail($pdfPath, 'Se ha generado el reporte de mayor analítico');

        Mail::to($email)->send($mailable);
    }

    /**
     * Metodo que se ejecuta cuando el trabajo falla.
     *
     * @return void
     */
    public function failed()
    {
        // @TODO: Implement failed() method.
    }

    /**
     * Método que filtra la lista de cuentas presupuestarias
     * @param array $budgetAccountsOpen Lista de cuentas presupuestarias abiertas
     * @param integer $initialCode Código inicial de la lista
     * @param integer $finalCode Código final de la lista
     * @param string $initialDate Fecha inicial de la lista
     * @param string $finalDate Fecha final de la lista
     *
     * @return array
     */
    public function filterBudgetAccounts(array $budgetAccountsOpen, int $initialCode, int $finalCode, string $initialDate, string $finalDate)
    {
        $filteredArray = [];

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
     * Obtiene las cuentas presupuestarias aperturadas
     *
     * @param bool $accountsWithMovements Indica si se debe obtener las cuentas presupuestarias abiertas con movimientos
     * @param object $project Proyecto asociado a las cuentas presupuestarias aperturadas
     * @param string $initialDate Fecha inicial de la consulta
     * @param string $finalDate Fecha final de la consulta
     *
     * @return array
     */
    public function getBudgetAccountsOpen(bool $accountsWithMovements, object $project, string $initialDate, string $finalDate)
    {
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
     * Obtiene información de los montos de las cuentas comprometidas
     *
     * @param object $accout_id Objeto con información de la cuenta
     * @param mixed $initialDate Fecha inicial de del compromiso
     * @param mixed $finalDate Fecha final del compromiso
     *
     * @return array
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
                        $compromises[$positiveKey]['date'] = Carbon::rawCreateFromFormat('Y-m-d', $com->budgetCompromise->compromised_at);
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
                            $compromises[$negativeKey]['date'] = Carbon::rawCreateFromFormat('Y-m-d', $com->budgetCompromise->compromised_at);
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
     * Obtiene información de los montos de las cuentas causadas
     *
     * @param object $account Datos de la cuenta
     * @param string $initialDate Fecha inicial del compromiso
     * @param string $finalDate Fecha final del compromiso
     *
     * @return array
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
                                    $amount['date'] = Carbon::rawCreateFromFormat('Y-m-d', $date);
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
                                        $amount['date'] = Carbon::rawCreateFromFormat('Y-m-d', $date);
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
     * @param object $account Datos de la cuenta
     *
     * @return float|array Monto del pagado para la cuenta presupuestaria $account     *
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
                                    $amount['date'] = Carbon::rawCreateFromFormat('Y-m-d', $date);
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
                                        $amount['date'] = Carbon::rawCreateFromFormat('Y-m-d', $date);
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

    /**
     * Obtiene información de las cuentas padre
     *
     * @param object $child Datos de la cuenta hija
     * @param array $parents Datos de las cuentas padres
     *
     * @return array
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
}
