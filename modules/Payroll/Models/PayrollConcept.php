<?php

namespace Modules\Payroll\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;
use Illuminate\Support\Str;

/**
 * @class      PayrollConcept
 * @brief      Datos de conceptos
 *
 * Gestiona el modelo de conceptos
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConcept extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name', 'description', 'active', 'formula', 'institution_id',
        'payroll_concept_type_id', 'payroll_salary_tabulator_id', 'is_strict',
        'accounting_account_id', 'budget_account_id', 'budget_project_id',
        'budget_centralized_action_id', 'budget_specific_action_id', 'assign_to',
        'currency_id', 'pay_order', 'arc'
    ];

    /**
     * Atributos personalizados a cargar en las consultas
     *
     * @var array $appends
     */
    protected $appends = ['translate_formula'];

    /**
     * Método que obtiene la información de la institución asociada al concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene la información de la moneda asociada al concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * Método que obtiene la información del tipo de concepto asociado al concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollConceptType()
    {
        return $this->belongsTo(PayrollConceptType::class);
    }

    /**
     * Método que obtiene la información del tabulador salarial asociado al concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSalaryTabulator()
    {
        return $this->belongsTo(PayrollSalaryTabulator::class);
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un género
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollConceptAssignOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'applicable');
    }

    /**
     * Método que obtiene la información de la cuenta contable asociada al concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
    }

    /**
     * Método que obtiene la información de la cuenta presupuestaria asociada al concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetAccount()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetAccount::class) : null;
    }

    /**
     * Método que obtiene la información del proyecto asociado al concepto
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetProject()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetProject::class) : null;
    }

    /**
     * Scope para buscar y filtrar datos de Parroquias
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(DB::raw('upper(name)'), 'LIKE', '%' . strtoupper($search) . '%');
    }

    /**
     * Método que obtiene la información de la acción centralizada asociada al concepto
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetCentralizedAction()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetCentralizedAction::class) : null;
    }

    /**
     * Método que obtiene la información de la acción específica asociada al concepto
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetSpecificAction()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetSpecificAction::class) : null;
    }

    /**
     * Método que obtiene los tipos de pago de nómina asociados a muchos conceptos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payrollPaymentTypes()
    {
        return $this->belongsToMany(
            PayrollPaymentType::class,
            'payroll_concept_payment_type',
            'payroll_concept_id',
            'payroll_payment_type_id'
        );
    }

    /**
     * Obtiene el atributo de la formula del concepto
     *
     * @return array|string
     */
    public function getTranslateFormulaAttribute()
    {
        $parameters = new PayrollAssociatedParametersRepository();
        $formula = str_replace('if', 'Si', $this->formula);
        $typesParameters = ['associatedBenefit', 'associatedVacation', 'associatedWorkerFile', 'parameter', 'concept', 'tabulator', 'ari_register'];

        foreach ($typesParameters as $typeParameter) {
            if (in_array($typeParameter, ['parameter', 'concept', 'tabulator', 'ari_register'])) {
                if ($typeParameter == 'parameter') {
                    $types = Parameter::where(
                        [
                            'required_by' => 'payroll',
                            'active'      => true,
                        ]
                    )->where('p_key', 'like', 'global_parameter_%')->get();
                    foreach ($types as $type) {
                        $jsonValue = json_decode($type->p_value);
                        $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->name, $formula);
                    }
                } elseif ($typeParameter == 'concept') {
                    $types = PayrollConcept::all();
                    foreach ($types as $type) {
                        $formula = str_replace('concept(' . $type['id'] . ')', $type['name'], $formula);
                    }
                } elseif ($typeParameter == 'tabulator') {
                    $types = PayrollSalaryTabulator::all();
                    foreach ($types as $type) {
                        $formula = str_replace('tabulator(' . $type['id'] . ')', $type['name'], $formula);
                    }
                } elseif ($typeParameter == 'ari_register') {
                    $formula = str_replace('ari_register', 'Registro ARI', $formula);
                }
            } else {
                $types = $parameters->loadData($typeParameter);
                foreach ($types as $type) {
                    if (empty($type['children'])) {
                        $formula = str_replace($type['id'], $type['name'], $formula);
                    } else {
                        foreach ($type['children'] as $children) {
                            $formula = str_replace($children['id'], $children['name'], $formula);
                        }
                    }
                }
            }
        }
        return $formula;
    }

    /**
     * Obtiene el atributo de la formula del concepto
     *
     * @return array|string
     */
    public function getTranslateBackFormula()
    {
        $parameters = new PayrollAssociatedParametersRepository();
        $formula = str_replace('if', 'Si', $this->formula);
        $typesParameters = ['associatedBenefit', 'associatedVacation', 'associatedWorkerFile', 'concept', 'parameter', 'tabulator', 'ari_register'];

        foreach ($typesParameters as $typeParameter) {
            if (in_array($typeParameter, ['parameter', 'concept', 'tabulator', 'ari_register'])) {
                if ($typeParameter == 'parameter') {
                    $types = Parameter::where(
                        [
                            'required_by' => 'payroll',
                            'active'      => true,
                        ]
                    )->where('p_key', 'like', 'global_parameter_%')->get();
                    foreach ($types as $type) {
                        $jsonValue = json_decode($type->p_value);
                        $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->name, $formula);
                    }
                } elseif ($typeParameter == 'concept') {
                    do {
                        $conceptIds = $this->getConceptIdsFromFormula($formula);
                        $types = PayrollConcept::query()->whereIn('id', $conceptIds)->get();

                        foreach ($types as $type) {
                            $formula = str_replace('concept(' . $type['id'] . ')', $type['formula'], $formula);
                        }
                    } while (count($this->getConceptIdsFromFormula($formula)) > 0);
                } elseif ($typeParameter == 'tabulator') {
                    $types = PayrollSalaryTabulator::all();
                    foreach ($types as $type) {
                        $formula = str_replace('tabulator(' . $type['id'] . ')', $type['name'], $formula);
                    }
                } elseif ($typeParameter == 'ari_register') {
                    $formula = str_replace('ari_register', 'Registro ARI', $formula);
                }
            } else {
                $types = $parameters->loadData($typeParameter);
                foreach ($types as $type) {
                    if (empty($type['children'])) {
                        $formula = str_replace($type['id'], $type['name'], $formula);
                    } else {
                        foreach ($type['children'] as $children) {
                            $formula = str_replace($children['id'], $children['name'], $formula);
                        }
                    }
                }
            }
        }
        return $formula;
    }

    /**
     * Obtiene los IDs de los conceptos desde la fórmula
     *
     * @param string $formula Datos de la fórmula
     *
     * @return array
     */
    public function getConceptIdsFromFormula($formula)
    {
        $pattern = '/concept\((\d+)\)/';
        preg_match_all($pattern, $formula, $matches);

        return isset($matches) ? $matches[1] : [];
    }
}
