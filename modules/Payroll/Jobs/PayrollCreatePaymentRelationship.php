<?php

namespace Modules\Payroll\Jobs;

use DateTime;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Parameter;
use Illuminate\Queue\SerializesModels;
use Modules\Payroll\Models\Institution;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Payroll\Models\PayrollStaff;
use App\Notifications\SystemNotification;
use Modules\Payroll\Models\PayrollConcept;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Models\PayrollAriRegister;
use Modules\Payroll\Models\PayrollConceptType;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Models\PayrollPaymentPeriod;
use Illuminate\Queue\MaxAttemptsExceededException;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;
use Modules\Payroll\Actions\PayrollPaymentRelationshipAction;
use Modules\Payroll\Exceptions\FailedPayrollConceptException;
use Modules\Payroll\Transformers\PayrollSalaryTabulatorResource;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;

/**
 * @class PayrollCreatePaymentRelationship
 * @brief Trabajo que se encarga de registrar la relación de pago de la nómina
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCreatePaymentRelationship implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0; //300; /** 5min */

    /**
     * Crea una nueva instancia del trabajo
     *
     * @return void
     */
    public function __construct(
        protected array $data,
        protected PayrollPaymentRelationshipAction $payrollPaymentAction = new PayrollPaymentRelationshipAction(),
    ) {
        if ('local' !== @env('APP_ENV')) {
            $this->onQueue('bulk');
        }
    }

    /**
     * Ejecuta el trabajo de registrar la nómina de sueldos
     *
     * @return void
     */
    public function handle()
    {
        try {
            $created_at = now();
            $payrollParameters = new PayrollAssociatedParametersRepository();
            $date = new DateTime($this->data['created_at']);
            $date->format('Y-m-d H:i:s');
            /* Objeto asociado al modelo Payroll */
            $payroll = Payroll::query()->create(
                [
                    'name' => $this->data['name'],
                    'status' => 'En Proceso',
                    'code' => $this->data['code'],
                    'payroll_payment_period_id' => $this->data['payroll_payment_period_id'],
                    'payroll_parameters' => json_encode($this->data['payroll_parameters']),
                ]
            );
            Log::info($payroll);
            $payroll->created_at = $date ?? $created_at;

            $this->data['payroll_parameters'] = $this->payrollPaymentAction->getPayrollParameters($payroll->id);

            PayrollStaffPayroll::query()
                ->where('payroll_id', $payroll->id)
                ->forceDelete();

            /* Se recorren los conceptos establecidos para la generación de la nómina */
            $concepts = [];
            $fullConcepts = array_merge(
                array_map(function ($item) {
                    $item['time_sheet'] = 'active';
                    return $item;
                }, $this->data['payroll_concepts'] ?? []),
                array_map(function ($item) {
                    $item['time_sheet'] = 'pending';
                    return $item;
                }, $this->data['pending_concepts'] ?? []),
            );
            foreach ($fullConcepts as $concept) {
                $formula = null;
                $payrollConcept = PayrollConcept::query()
                    ->with('payrollConceptAssignOptions')
                    ->find($concept['id']);
                $formula = $this->translateFormConcept($payrollConcept->formula);
                $exploded = multiexplode(
                    [
                        'if', '(', ')', '{', '}',
                        '==', '<=', '>=', '<', '>', '!=',
                        '+', '-', '*', '/', 'select', 'case',
                        'when', 'else', 'end', ';', 'then',
                        ',', '.',
                    ],
                    $formula
                );
                while (count($exploded) > 0) {
                    $complete = false;
                    $current = max_length($exploded);
                    $key = array_search($current, $exploded);
                    /** Se descartan los elementos vacios y las constantes númericas */
                    if ($current == '' || is_numeric($current)) {
                        unset($exploded[$key]);
                        $complete = true;
                    } else {
                        /* Se recorre el listado de parámetros para sustituirlos por su valor real en la formula del concepto */
                        foreach ($this->data['payroll_parameters'] as $parameter) {
                            if (gettype($parameter) == 'object') {
                                $parameter = (array)$parameter;
                            }
                            if (
                                isset($parameter['time_sheet']) &&
                                $parameter['time_sheet'] != $concept['time_sheet']
                            ) {
                                continue;
                            }

                            if ($parameter['name'] == $current) {
                                if (! isset($parameter['staff_id'])) {
                                    unset($exploded[$key]);
                                    $complete = true;
                                    $formula = str_replace($parameter['name'], $parameter['value'], $formula);
                                }
                            }
                        }
                        if ($complete == false) {
                            /* Se descartan los parametro de vacaciones y los del expediente del trabajador para ser analizados mas adelante */
                            unset($exploded[$key]);
                            $complete = true;
                        }
                    }
                }
                array_push(
                    $concepts,
                    [
                        'field' => $payrollConcept,
                        'formula' => $formula,
                        'time_sheet' => $concept['time_sheet'],
                        'staffs' => $concept['staffs'] ?? [],
                    ]
                );
            }

            /* Se guardan los tabuladores salariales usados en la nómina */
            $payroll->salary_tabulators = getPayrollSalaryTabulators($concepts);

            $extraOptions = [];
            foreach ($concepts as $concept) {
                foreach ($concept['field']->payrollConceptAssignOptions->where('key', 'staff') as $assign_option) {
                    $extraOptions[$concept['field']->id][] = $assign_option['assignable_id'];
                }
            }
            $staffsPending = array_reduce($this->data['pending_concepts'], function ($carry, $concept) {
                return array_merge($carry, $concept['staffs']);
            }, []);
            $exceptionStaffs = array_unique(array_merge($staffsPending, ...$extraOptions));
            /* Se evaluan los parámetros del expediente del trabajador y de la configuración de vacaciones */
            /* Se identifica la institución en la que se está operando */
            $institution = Institution::query()
                ->when(! empty($this->data['institution_id']), function ($query) {
                    $query->where('id', $this->data['institution_id']);
                }, function ($query) {
                    $query->where('active', true)->where('default', true);
                })
                ->first();
            /* Se obtienen todos los trabajadores asociados a la institución y se evalua si aplica cada uno de los conceptos */
            $period = PayrollPaymentPeriod::find($this->data['payroll_payment_period_id']);
            $period_start = $period?->start_date;
            $period_end = $period?->end_date;

            /* Se obtienen todos los trabajadores asociados a la institución y se evalua si aplica cada uno de los conceptos */
            $payrollStaffs = PayrollStaff::query()
                ->whereHas('payrollEmployment', function ($q) use ($institution, $period_end) {

                    $q->whereHas('department', function ($qq) use ($institution) {
                        $qq->where('institution_id', $institution->id);
                    })
                    ->where('start_date', '<=', $period_end);
                })
                ->orWhereIn('id', $exceptionStaffs);

            $payrollStaffs = $payrollStaffs->orderBy('first_name')->get();
            $assignTo = $payrollParameters->loadData('assignTo');

            $types = [];
            foreach ($payrollStaffs as $payrollStaff) {
                /* Se definen los arreglos de asignaciones y deducciones para clasificar los conceptos */
                $conceptTypes = PayrollConceptType::query()
                    ->get('name');

                foreach ($conceptTypes as $conceptType) {
                    $types[$conceptType->name] = [];
                }
                foreach ($concepts as $concept) {
                    $conceptAssignTo = json_decode($concept['field']['assign_to']);
                    if ($concept['field']['is_strict'] ?? false) {
                        $conceptAssignTo = array_chunk($conceptAssignTo, 1);
                        $verify = true;
                        foreach ($conceptAssignTo as $key => $value) {
                            if (
                                false == verify_assignment(
                                    $value,
                                    $assignTo,
                                    $concept['field']->payrollConceptAssignOptions,
                                    $payrollStaff->id,
                                    $period_start,
                                    $period_end,
                                    (in_array($payrollStaff->id, $extraOptions[$concept['field']->id] ?? [])) ? array($payrollStaff->id) : [],
                                )
                            ) {
                                $verify = false;
                            }
                        }
                        $verify = isset($extraOptions[$concept['field']->id])
                            ? ($verify && in_array($payrollStaff->id, $extraOptions[$concept['field']->id] ?? []))
                            : $verify;
                    } else {
                        $verify = verify_assignment(
                            $conceptAssignTo,
                            $assignTo,
                            $concept['field']->payrollConceptAssignOptions,
                            $payrollStaff->id,
                            $period_start,
                            $period_end,
                            $exceptionStaffs
                        );

                        $verify = isset($extraOptions[$concept['field']->id])
                            ? ($verify || in_array($payrollStaff->id, $extraOptions[$concept['field']->id] ?? []))
                            : $verify;
                    }

                    if ($verify) {
                        if (($concept['time_sheet'] == 'pending') && !in_array($payrollStaff->id, $concept['staffs'])) {
                            $concept['field']->load('payrollConceptType');
                            array_push($types[$concept['field']->payrollConceptType->name], [
                                'id' => $concept['field']->id ?? '',
                                'name' => $concept['field']->name,
                                'value' => 0,
                                'time_sheet' => $concept['time_sheet'] ?? '',
                                'sign' => $concept['field']->payrollConceptType['sign'],
                                'accouting_account_id' => $concept['field']->accounting_account_id ?? '',
                                'budget_account_id' => $concept['field']->budget_account_id ?? '',
                                'budget_account_code' => $concept['field']->budgetAccount->code ?? '',
                                'budget_account_denomination' => $concept['field']->budgetAccount->denomination ?? '',
                                'accounting_account_code' => $concept['field']->accountingAccount->code ?? '',
                                'accounting_account_denomination' => $concept['field']->accountingAccount->denomination ?? '',
                                'formula' => $concept['field']->translate_formula ?? '',

                            ]);
                        } else {
                            $types = $this->setFormula(
                                $payrollStaff,
                                $concept,
                                $payrollParameters,
                                $institution,
                                $types,
                                $period_start,
                                $period_end
                            );
                        }
                    } else {
                        /* Se carga la propiedad payrollConceptType para determinar como clasificar el concepto */
                        $concept['field']->load('payrollConceptType');
                        array_push($types[$concept['field']->payrollConceptType->name], [
                            'id' => $concept['field']->id ?? '',
                            'name' => $concept['field']->name,
                            'value' => 0,
                            'time_sheet' => $concept['time_sheet'] ?? '',
                            'sign' => $concept['field']->payrollConceptType['sign'],
                            'accouting_account_id' => $concept['field']->accounting_account_id ?? '',
                            'budget_account_id' => $concept['field']->budget_account_id ?? '',
                            'budget_account_code' => $concept['field']->budgetAccount->code ?? '',
                            'budget_account_denomination' => $concept['field']->budgetAccount->denomination ?? '',
                            'accounting_account_code' => $concept['field']->accountingAccount->code ?? '',
                            'accounting_account_denomination' => $concept['field']->accountingAccount->denomination ?? '',
                            'formula' => $concept['field']->translate_formula ?? '',
                        ]);
                    }
                }

                $add = false;
                foreach ($types as $type) {
                    foreach ($type as $t) {
                        if ($t['value'] > 0) {
                            $add = true;
                        }
                    }
                }

                if ($add == true) {
                    $basic_payroll_staff_data = loadBasicPayrollStaffData($payrollStaff, $period_end);
                    PayrollStaffPayroll::create(
                        [
                            'payroll_id' => $payroll->id,
                            'payroll_staff_id' => $payrollStaff->id,
                            'concept_type' => $types,
                            'basic_payroll_staff_data' => $basic_payroll_staff_data ?? [],
                        ]
                    );
                }
            }

            /* Se capturan los conceptos de la nómina */
            $payroll->concept_types = $types;
            $payroll->status = "Completado";
            $payroll->save();

            $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
            $user->notify(new SystemNotification('Exito', 'Nomina ejecutada con exito'));
        } catch (\Exception $e) {
            $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
            Log::critical("Se generó un error en el procesamiento de la nómina en el archivo [{$e->getFile()}] en la línea [{$e->getLine()}]. Código del error: {$e->getCode()}, Detalles: {$e->getMessage()}.\n Se muestra a continuación una traza de los archivos que generaron el error: {$e->getTraceAsString()}");
            if ($e instanceof FailedPayrollConceptException) {
                $user->notify(
                    new SystemNotification('Fallido', 'Ah ocurrido un error en la ejecución de la nómina ' .
                        $e->getMessage())
                );
            } else {
                $user->notify(new SystemNotification('Alerta', 'Se generó un error al generar la nómina, Contacte al administrador del sistema.'));
            }
        }
    }

    /**
     * Traduce la fórmula de conceptos
     *
     * @param string $form Fórmula del concepto
     *
     * @return string
     */
    public function translateFormConcept($form)
    {
        $formula = $form;
        /* Se hace la busqueda de los parámetros globales */
        $parameters = Parameter::query()
            ->where(
                [
                    'required_by' => 'payroll',
                    'active' => true,
                ]
            )
            ->where('p_key', 'like', 'global_parameter_%')
            ->get();
        foreach ($parameters as $parameter) {
            $jsonValue = json_decode($parameter->p_value);
            if (
                $jsonValue->parameter_type == 'resettable_variable' ||
                $jsonValue->parameter_type == 'time_parameter' ||
                $jsonValue->parameter_type == 'processed_variable'
            ) {
                $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->name, $formula);
            } else {
                if ($jsonValue->percentage) {
                    $jsonValue->value = $jsonValue->value / 100;
                }
                $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->value, $formula);
            }
        }
        /* Se hace la busqueda de los conceptos */
        $matchs = [];
        preg_match_all("/concept\([0-9]+\)/", $formula, $matchs);

        foreach ($matchs[0] as $match) {
            $id = substr($match, (strpos($match, '(') + 1), strpos($match, ')') - (strpos($match, '(') + 1));
            $concept = PayrollConcept::find($id);
            $formula = str_replace('concept(' . $id . ')', $this->translateFormConcept($concept['formula']), $formula);
        }

        return '(' . $formula . ')';
    }

    /**
     * Establece la fórmula del concepto
     *
     * @param PayrollStaff $payrollStaff
     * @param PayrollConcept $concept
     * @param object $payrollParameters
     * @param Institution $institution
     * @param mixed $types
     * @param string $period_start
     * @param string $period_end
     *
     * @throws \Modules\Payroll\Exceptions\FailedPayrollConceptException
     *
     * @return mixed
     */
    private function setFormula(
        $payrollStaff,
        $concept,
        $payrollParameters,
        $institution,
        $types,
        $period_start,
        $period_end
    ) {
        foreach ($this->data['payroll_parameters'] as $parameter) {
            if (gettype($parameter) == 'object') {
                $parameter = (array)$parameter;
            }
            if (
                isset($parameter['time_sheet']) &&
                $parameter['time_sheet'] != $concept['time_sheet']
            ) {
                continue;
            }
            if (isset($parameter['staff_id']) && $parameter['staff_id'] == $payrollStaff->id) {
                $concept['formula'] = str_replace($parameter['name'], $parameter['value'], $concept['formula']);
            }
        }

        $formula = $concept['formula'];
        /* Se hace la busqueda de los tabuladores */
        $matchs = [];
        preg_match_all("/tabulator\([0-9]+\)/", $formula, $matchs);
        foreach ($matchs[0] as $match) {
            $id = substr($match, (strpos($match, '(') + 1), strpos($match, ')') - (strpos($match, '(') + 1));
            $payrollSalaryTabulator = PayrollSalaryTabulator::find($id);

            if ($payrollSalaryTabulator->payroll_salary_tabulator_type == 'horizontal') {
                /* Se carga el escalafón horizontal asociado al tabulador */
                $payrollSalaryTabulator->load(['payrollHorizontalSalaryScale' => function ($q) {
                    $q->with('payrollScales');
                }]);
                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                    if (! empty($parameter['children'])) {
                        foreach ($parameter['children'] as $children) {
                            if ($children['id'] == $payrollSalaryTabulator->payrollHorizontalSalaryScale['group_by']) {
                                $record = ($parameter['model'] != PayrollStaff::class)
                                    ? $parameter['model']::query()
                                        ->where('payroll_staff_id', $payrollStaff->id)
                                        ->first()
                                    : $payrollStaff;
                                if (isset($record)) {
                                    foreach (
                                        $payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales as $scale
                                    ) {
                                        if ($children['type'] == 'number') {
                                            /* Se calcula el número de registros existentes según sea el caso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            $record->loadCount($children['required'][0]);

                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    ($record[Str::snake($children['required'][0]) . '_count'] >= $scl->from) &&
                                                    ($record[Str::snake($children['required'][0]) . '_count'] <= $scl->to)
                                                ) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                        ->where('payroll_vertical_scale_id', null)
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            } else {
                                                if ($scl == $record[Str::snake($children['required'][0]) . '_count']) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                        ->where('payroll_vertical_scale_id', null)
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            }
                                        } elseif ($children['type'] == 'date') {
                                            /* Se calcula el número de años según la fecha de ingreso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    (age($record[$children['required'][0]], $period_end, true) >= $scl->from) &&
                                                    (age($record[$children['required'][0]], $period_end, true) <= $scl->to)
                                                ) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                        ->where('payroll_vertical_scale_id', null)
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            } else {
                                                if ($scl == age($record[$children['required'][0]], $period_end)) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                        ->where('payroll_vertical_scale_id', null)
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            }
                                        } else {
                                            /* Se identifica el valor según el expediente del trabajador
                                            y se sustituye por su valor en el tabulador */
                                            if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                $tabScale = PayrollSalaryTabulatorScale::query()
                                                    ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                    ->where('payroll_horizontal_scale_id', $scale['id'])
                                                    ->where('payroll_vertical_scale_id', null)
                                                    ->first();

                                                if ($payrollSalaryTabulator->percentage) {
                                                    $formula = str_replace(
                                                        $match,
                                                        $tabScale['value'] / 100,
                                                        $formula ?? $concept['formula']
                                                    );
                                                } else {
                                                    $formula = str_replace(
                                                        $match,
                                                        $tabScale['value'],
                                                        $formula ?? $concept['formula']
                                                    );
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $formula = str_replace(
                                        $match,
                                        0,
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                        }
                    }
                }
            } elseif ($payrollSalaryTabulator->payroll_salary_tabulator_type == 'vertical') {
                /* Se carga el escalafón vertical asociado al tabulador */
                $payrollSalaryTabulator->load(['payrollVerticalSalaryScale' => function ($q) {
                    $q->with('payrollScales');
                }]);
                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                    if (! empty($parameter['children'])) {
                        foreach ($parameter['children'] as $children) {
                            if ($children['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                $record = ($parameter['model'] != PayrollStaff::class)
                                    ? $parameter['model']::query()
                                        ->where('payroll_staff_id', $payrollStaff->id)
                                        ->first()
                                    : $payrollStaff;
                                if (isset($record)) {
                                    foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scale) {
                                        if ($children['type'] == 'number') {
                                            /* Se calcula el número de registros existentes según sea el caso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            $record->loadCount($children['required'][0]);

                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    ($record[Str::snake($children['required'][0]) . '_count'] >= $scl->from) &&
                                                    ($record[Str::snake($children['required'][0]) . '_count'] <= $scl->to)
                                                ) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', null)
                                                        ->where('payroll_vertical_scale_id', $scale['id'])
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            } else {
                                                if ($scl == $record[Str::snake($children['required'][0]) . '_count']) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', null)
                                                        ->where('payroll_vertical_scale_id', $scale['id'])
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            }
                                        } elseif ($children['type'] == 'date') {
                                            /* Se calcula el número de años según la fecha de ingreso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    (age($record[$children['required'][0]], $period_end, true) >= $scl->from) &&
                                                    (age($record[$children['required'][0]], $period_end, true) <= $scl->to)
                                                ) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', null)
                                                        ->where('payroll_vertical_scale_id', $scale['id'])
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            } else {
                                                if ($scl == age($record[$children['required'][0]], $period_end)) {
                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', null)
                                                        ->where('payroll_vertical_scale_id', $scale['id'])
                                                        ->first();
                                                    if ($payrollSalaryTabulator->percentage) {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'] / 100,
                                                            $formula ?? $concept['formula']
                                                        );
                                                    } else {
                                                        $formula = str_replace(
                                                            $match,
                                                            $tabScale['value'],
                                                            $formula ?? $concept['formula']
                                                        );
                                                    }
                                                }
                                            }
                                        } else {
                                            /* Se identifica el valor según el expediente del trabajador
                                            y se sustituye por su valor en el tabulador */
                                            if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                $tabScale = PayrollSalaryTabulatorScale::query()
                                                    ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                    ->where('payroll_horizontal_scale_id', null)
                                                    ->where('payroll_vertical_scale_id', $scale['id'])
                                                    ->first();

                                                if ($payrollSalaryTabulator->percentage) {
                                                    $formula = str_replace(
                                                        $match,
                                                        $tabScale['value'] / 100,
                                                        $formula ?? $concept['formula']
                                                    );
                                                } else {
                                                    $formula = str_replace(
                                                        $match,
                                                        $tabScale['value'],
                                                        $formula ?? $concept['formula']
                                                    );
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $formula = str_replace(
                                        $match,
                                        0,
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                /* Se cargan los escalafones horizontal y vertical asociados al tabulador */
                $payrollSalaryTabulator->load([
                    'payrollHorizontalSalaryScale' => function ($q) {
                        $q->with('payrollScales');
                    }, 'payrollVerticalSalaryScale' => function ($q) {
                        $q->with('payrollScales');
                    },
                ]);
                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                    if (! empty($parameter['children'])) {
                        foreach ($parameter['children'] as $children) {
                            if ($children['id'] == $payrollSalaryTabulator->payrollHorizontalSalaryScale['group_by']) {
                                $record = ($parameter['model'] != PayrollStaff::class)
                                    ? $parameter['model']::query()
                                        ->where('payroll_staff_id', $payrollStaff->id)
                                        ->first()
                                    : $payrollStaff;
                                if (isset($record)) {
                                    foreach ($payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales as $scale) {
                                        if ($children['type'] == 'number') {
                                            /* Se calcula el número de registros existentes según sea el caso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            $record->loadCount($children['required'][0]);

                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    ($record[Str::snake($children['required'][0]) . '_count'] >= $scl->from) &&
                                                    ($record[Str::snake($children['required'][0]) . '_count'] <= $scl->to)
                                                ) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();

                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                if ($scl == $record[Str::snake($children['required'][0]) . '_count']) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();

                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif ($children['type'] == 'date') {
                                            /* Se calcula el número de años según la fecha de ingreso
                                            y se sustituye por su valor en el tabulador */
                                            $scl = json_decode($scale['value']);
                                            if (isset($scl->from) && isset($scl->to)) {
                                                if (
                                                    (age($record[$children['required'][0]], $period_end, true) >= $scl->from) &&
                                                    (age($record[$children['required'][0]], $period_end, true) <= $scl->to)
                                                ) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();

                                                                                    if (isset($tabScale)) {
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                if ($scl == age($record[$children['required'][0]], $period_end)) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (! empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::query()
                                                                            ->where('payroll_staff_id', $payrollStaff->id)
                                                                            ->first()
                                                                        : $payrollStaff;
                                                                    if (isset($recordV)) {
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);

                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                        ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV->from) && isset($sclV->to)) {
                                                                                    if (
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                        (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                            ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                            ->first();
                                                                                        if ($payrollSalaryTabulator->percentage) {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'] / 100,
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        } else {
                                                                                            $formula = str_replace(
                                                                                                $match,
                                                                                                $tabScale['value'],
                                                                                                $formula ?? $concept['formula']
                                                                                            );
                                                                                        }
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();

                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $formula = str_replace(
                                                                            $match,
                                                                            0,
                                                                            $formula ?? $concept['formula']
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            /* Se identifica el valor según el expediente del trabajador
                                            y se sustituye por su valor en el tabulador */
                                            if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                    if (! empty($parameterV['children'])) {
                                                        foreach ($parameterV['children'] as $childrenV) {
                                                            if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                    ? $parameterV['model']::query()
                                                                        ->where('payroll_staff_id', $payrollStaff->id)
                                                                        ->first()
                                                                    : $payrollStaff;
                                                                if (isset($recordV)) {
                                                                    foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                        if ($childrenV['type'] == 'number') {
                                                                            /* Se calcula el número de registros existentes según sea el caso
                                                                            y se sustituye por su valor en el tabulador */
                                                                            $sclV = json_decode($scaleV['value']);
                                                                            $recordV->loadCount($childrenV['required'][0]);

                                                                            if (isset($sclV->from) && isset($sclV->to)) {
                                                                                if (
                                                                                    ($recordV[Str::snake($childrenV['required'][0]) . '_count'] >= $sclV->from) &&
                                                                                    ($recordV[Str::snake($childrenV['required'][0]) . '_count'] <= $sclV->to)
                                                                                ) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();
                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                if ($sclV == $recordV[Str::snake($childrenV['required'][0]) . '_count']) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();
                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        } elseif ($childrenV['type'] == 'date') {
                                                                            /* Se calcula el número de años según la fecha de ingreso
                                                                            y se sustituye por su valor en el tabulador */
                                                                            $sclV = json_decode($scaleV['value']);
                                                                            if (isset($sclV->from) && isset($sclV->to)) {
                                                                                if (
                                                                                    (age($recordV[$childrenV['required'][0]], $period_end, true) >= $sclV->from) &&
                                                                                    (age($recordV[$childrenV['required'][0]], $period_end, true) <= $sclV->to)
                                                                                ) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();
                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                if ($sclV == age($recordV[$childrenV['required'][0]], $period_end)) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                        ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                        ->first();
                                                                                    if ($payrollSalaryTabulator->percentage) {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'] / 100,
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    } else {
                                                                                        $formula = str_replace(
                                                                                            $match,
                                                                                            $tabScale['value'],
                                                                                            $formula ?? $concept['formula']
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        } else {
                                                                            /* Se identifica el valor según el expediente del trabajador
                                                                            y se sustituye por su valor en el tabulador */
                                                                            if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                $tabScale = PayrollSalaryTabulatorScale::query()
                                                                                    ->where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                    ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                    ->where('payroll_vertical_scale_id', $scaleV['id'])
                                                                                    ->first();

                                                                                if ($payrollSalaryTabulator->percentage) {
                                                                                    $formula = str_replace(
                                                                                        $match,
                                                                                        $tabScale['value'] / 100,
                                                                                        $formula ?? $concept['formula']
                                                                                    );
                                                                                } else {
                                                                                    $formula = str_replace(
                                                                                        $match,
                                                                                        $tabScale['value'],
                                                                                        $formula ?? $concept['formula']
                                                                                    );
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $formula = str_replace(
                                                                        $match,
                                                                        0,
                                                                        $formula ?? $concept['formula']
                                                                    );
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $formula = str_replace(
                                        $match,
                                        0,
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        /* Si no se encuentra resultado se envian a cero los tabuladores */
        $matchs = [];
        preg_match_all("/tabulator\([0-9]+\)/", $formula, $matchs);
        foreach ($matchs[0] as $match) {
            $formula = str_replace(
                $match,
                0,
                $formula ?? $concept['formula']
            );
        }
        /* Fin de la busqueda */
        $exploded = multiexplode(
            [
                'if', '(', ')', '{', '}',
                '==', '<=', '>=', '<', '>', '!=',
                '+', '-', '*', '/', 'select', 'case',
                'when', 'else', 'end', ';', 'then',
                ',', '.',
            ],
            $formula
        );
        while (count($exploded) > 0) {
            $complete = false;
            $current = max_length($exploded);
            $key = array_search($current, $exploded);
            /* Se descartan los elementos vacios y las constantes númericas */
            if ($current == '' || is_numeric($current)) {
                unset($exploded[$key]);
                $complete = true;
            } else {
                /* Se recorre el listado de parámetros asociados a la configuración de prestaciones sociales
                para sustituirlos por su valor real en la formula del concepto */
                if ($complete == false) {
                    foreach ($payrollParameters->loadData('associatedBenefit') as $parameter) {
                        if ($parameter['id'] == $current) {
                            $record = (is_object($parameter['model']))
                                ? $parameter['model']
                                : $parameter['model']::query()
                                    ->where('institution_id', $institution->id)
                                    ->first();
                            unset($exploded[$key]);
                            $complete = true;
                            if (isset($record)) {
                                if ($parameter['id'] == 'BENEFIT_ADDITIONAL_DAYS_PER_YEAR') {
                                    $employment = $payrollStaff->payrollEmployment;
                                    $year = (age(($employment['start_date'] ?? $period_start), $period_end));
                                    $increment = $record[$parameter['required'][0]] *
                                        ($year - (($record[$parameter['minimum'][0]] ?? 0) - 1) ?? 0);
                                    $formula = str_replace(
                                        $parameter['id'],
                                        ($year < $record['minimum_number_years'])
                                            ? 0
                                            : ((($increment ?? 0) > $record['additional_maximum_days_per_year'])
                                                ? $record['additional_maximum_days_per_year']
                                                : ($increment ?? 0)),
                                        $formula ?? $concept['formula']
                                    );
                                } else {
                                    $formula = str_replace(
                                        $parameter['id'],
                                        $record[$parameter['required'][0]],
                                        $formula ?? $concept['formula']
                                    );
                                }
                            } else {
                                $formula = str_replace(
                                    $parameter['id'],
                                    0,
                                    $formula ?? $concept['formula']
                                );
                            }
                        }
                    }
                }
                /* Se recorre el listado de parámetros asociados a la configuración de vacaciones
                para sustituirlos por su valor real en la formula del concepto */
                if ($complete == false) {
                    foreach ($payrollParameters->loadData('associatedVacation') as $parameter) {
                        if ($parameter['id'] == $current) {
                            $record = (is_object($parameter['model']))
                                ? $parameter['model']
                                : $parameter['model']::query()
                                    ->where('institution_id', $institution->id)
                                    ->first();
                            unset($exploded[$key]);
                            $complete = true;
                            if (isset($record)) {
                                $formula = str_replace(
                                    $parameter['id'],
                                    $record[$parameter['required'][0]],
                                    $formula ?? $concept['formula']
                                );
                            } else {
                                $formula = str_replace(
                                    $parameter['id'],
                                    0,
                                    $formula ?? $concept['formula']
                                );
                            }
                        }
                    }
                }
                /* Se recorre el listado de parámetros asociados al expediente del trabajador
                para sustituirlos por su valor real en la formula del concepto */
                if ($complete == false) {
                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                        if (! empty($parameter['children'])) {
                            foreach ($parameter['children'] as $children) {
                                if ($children['id'] == $current) {
                                    $record = ($parameter['model'] != PayrollStaff::class)
                                        ? $parameter['model']::query()
                                            ->where('payroll_staff_id', $payrollStaff->id)
                                            ->first()
                                        : $payrollStaff;
                                    unset($exploded[$key]);
                                    $complete = true;
                                    if ($children['type'] == 'number') {
                                        /* Se calcula el número de registros existentes según sea el caso
                                        y se sustituye por su valor real en la fórmula del concepto */
                                        if (isset($record)) {
                                            $number_children = 0;
                                            if ($children['id'] == 'NUMBER_CHILDREN') {
                                                foreach (
                                                    $concept['field']
                                                        ->payrollConceptAssignOptions
                                                        ->where('key', 'all_staff_with_sons') as $assign_option
                                                ) {
                                                    $range = multiexplode(
                                                        ['"', "'", 'minimum', ',', 'maximum', '{', '}', ':'],
                                                        $assign_option['value']
                                                    );
                                                    $values = array_reduce($range, function ($val, $i) {
                                                        if (isset($i) && $i != '') {
                                                            $val[] = $i;
                                                        }

                                                        return $val;
                                                    }, []);
                                                }
                                                $relationshipSon = \Modules\Payroll\Models\PayrollRelationship::where('name', 'Hijo(a)')->first();
                                                if (!isset($relationshipSon)) {
                                                    $relationshipSon["id"] = 3;
                                                }
                                                foreach ($record[$children['required'][0]] as $child) {
                                                    if ($child->payroll_relationships_id === $relationshipSon["id"]) {
                                                        $age = age($child->birthdate, $period_end, true);
                                                        if (isset($values) && count($values) > 0) {
                                                            if ($age >= $values[0] && $age <= $values[1]) {
                                                                $number_children++;
                                                            }
                                                        } else {
                                                            $number_children++;
                                                        }
                                                    }
                                                }
                                                $formula = str_replace(
                                                    $children['id'],
                                                    $number_children,
                                                    $formula ?? $concept['formula']
                                                );
                                            } else {
                                                $record->loadCount($children['required'][0]);
                                                $formula = str_replace(
                                                    $children['id'],
                                                    $number_children > 0
                                                    ? $number_children
                                                    : $record[Str::snake($children['required'][0]) . '_count'],
                                                    $formula ?? $concept['formula']
                                                );
                                            }
                                        } else {
                                            $formula = str_replace(
                                                $children['id'],
                                                0,
                                                $formula ?? $concept['formula']
                                            );
                                        }
                                    } elseif ($children['type'] == 'date') {
                                        /* Se calcula el número de años según la fecha de ingreso
                                        y se sustituye por su valor real en la fórmula del concepto */
                                        if (isset($record)) {
                                            $formula = str_replace(
                                                $children['id'],
                                                age($record[$children['required'][0]], $period_end, true),
                                                $formula ?? $concept['formula']
                                            );
                                        } else {
                                            $formula = str_replace(
                                                $children['id'],
                                                0,
                                                $formula ?? $concept['formula']
                                            );
                                        }
                                    } else {
                                        /* Se identifica el valor según el expediente del trabajador
                                        y se sustituye por su valor real en la fórmula del concepto */
                                        if (isset($record)) {
                                            $formula = str_replace(
                                                $children['id'],
                                                $record[$children['required'][0]],
                                                $formula ?? $concept['formula']
                                            );
                                        } else {
                                            $formula = str_replace(
                                                $children['id'],
                                                0,
                                                $formula ?? $concept['formula']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                /* Se descartan todos los demas parámetros */
                if ($complete == false) {
                    $coincidences = [];
                    $patterns = [
                        "/concept\([0-9]+\)/",
                        "/parameter\([0-9]+\)/",
                        "/tabulator\([0-9]+\)/",
                    ];

                    foreach ($patterns as $pattern) {
                        preg_match_all($pattern, $formula ?? $concept['formula'], $matches);

                        foreach ($matches[0] as $match) {
                            $coincidences[] = $match;
                        }
                    }
                    if (count($coincidences) > 0) {
                        throw new FailedPayrollConceptException("Error en la fórmula del concepto: " . $concept['field']->name);
                    }
                    $ari = ("ari_register" === $exploded[$key])
                        ? PayrollAriRegister::query()
                            ->where(function ($query) use ($period_end, $payrollStaff) {
                                $query->where('payroll_staff_id', $payrollStaff->id)
                                    ->whereNull('to_date')
                                    ->where('from_date', '<=', $period_end);
                            })
                            ->orWhere(function ($query) use ($period_end, $payrollStaff) {
                                $query->where('payroll_staff_id', $payrollStaff->id)
                                    ->whereNotNull('to_date')
                                    ->where('from_date', '<=', $period_end)
                                    ->where('to_date', '>=', $period_end);
                            })->first()
                        : null;
                    $formula = str_replace($exploded[$key], $ari?->percetage ?? 0, $formula ?? $concept['formula']);
                    unset($exploded[$key]);
                    $complete = true;
                }
            }
        }
        /* Se carga la propiedad payrollConceptType para determinar como clasificar el concepto */
        $concept['field']->load('payrollConceptType');
        $formula = expression_format($formula ?? $concept['formula']);
        array_push($types[$concept['field']->payrollConceptType->name], [
            'id' => $concept['field']->id ?? '',
            'name' => $concept['field']->name,
            'value' => str_eval($formula),
            'time_sheet' => $concept['time_sheet'] ?? '',
            'sign' => $concept['field']->payrollConceptType['sign'],
            'accouting_account_id' => $concept['field']->accounting_account_id ?? '',
            'budget_account_id' => $concept['field']->budget_account_id ?? '',
            'budget_account_code' => $concept['field']->budgetAccount->code ?? '',
            'budget_account_denomination' => $concept['field']->budgetAccount->denomination ?? '',
            'accounting_account_code' => $concept['field']->accountingAccount->code ?? '',
            'accounting_account_denomination' => $concept['field']->accountingAccount->denomination ?? '',
            'formula' => $concept['field']->translate_formula ?? '',
        ]);

        return $types;
    }

    /**
     * Maneja el fallo del trabajo.
     *
     * @param \Exception $exception Excepción generada al ocurrir un error en el trabajo
     *
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $user = User::without(['roles', 'permissions'])->where('id', $this->data['user_id'])->first();
        if ($exception instanceof MaxAttemptsExceededException) {
            $user->notify(
                new SystemNotification(
                    'Fallido',
                    'Ah ocurrido un error en la ejecución de la nómina, ' .
                    'para mas información comuniquese con el administrador.'
                )
            );
        } else {
            $user->notify(
                new SystemNotification('Fallido', 'Ah ocurrido un error en la ejecución de la nómina ' .
                $exception->getMessage())
            );
        }
        Log::error($exception->getMessage());
    }
}
