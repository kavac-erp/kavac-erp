<?php

namespace Modules\Payroll\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class PayrollScaleRequirements
 * @brief Reglas de validación para los requerimientos de las escalas de un escalafón salarial
 *
 * Gestiona las reglas de validación de los requerimientos de las escalas de un escalafón salarial
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollScaleRequirements implements Rule
{
    /**
     * Define el atributo que fallo la validación
     *
     * @var string $attribute
     */
    protected $attribute;

    /**
     * Define si el escalafón salarial es validado por grado de instrucción o por cargo
     *
     * @var string $group_by_clasification
     */
    protected $group_by_clasification;

    /**
     * Define si el escalafón salarial es validado por eperiencia laboral o antiguedad
     *
     * @var string $group_by_years
     */
    protected $group_by_years;

    /**
     * Crea una nueva instancia de la regla.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct($group_by_years = null, $group_by_clasification = null)
    {
        $this->group_by_years         = $group_by_years;
        $this->group_by_clasification = $group_by_clasification;
    }

    /**
     * Determina si la regla de validación es correcta.
     *
     * @param  string  $attribute Atributo a verificar
     * @param  mixed  $value Valor del atributo a verificar
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ((array) $value as $payrollScale) {
            if (is_null($payrollScale['name'])) {
                $this->attribute = 'nombre';
                return false;
            }
            if (is_null($payrollScale['code'])) {
                $this->attribute = 'código';
                return false;
            }
            if (($this->group_by_clasification == null) && ($this->group_by_years != null)) {
                if (is_array($payrollScale['payroll_scale_requirements'])) {
                    foreach ($payrollScale['payroll_scale_requirements'] as $field) {
                        if (is_null($field['scale_years_minimum'])) {
                            $this->attribute = 'cantidad minima de años';
                            return false;
                        };
                        if (is_null($field['scale_years_maximum'])) {
                            $this->attribute = 'cantidad maxima de años';
                            return false;
                        }
                    }
                }
            } elseif (($this->group_by_clasification == 'position') && ($this->group_by_years == null)) {
                if (is_array($payrollScale['payroll_scale_requirements'])) {
                    foreach ($payrollScale['payroll_scale_requirements'] as $field) {
                        if (is_null($field['clasificable_id'])) {
                            $this->attribute = 'cargo';
                            return false;
                        }
                    }
                }
            } elseif (($this->group_by_clasification == 'instruction_degree') && ($this->group_by_years == null)) {
                if (is_array($payrollScale['payroll_scale_requirements'])) {
                    foreach ($payrollScale['payroll_scale_requirements'] as $field) {
                        if (is_null($field['clasificable_id'])) {
                            $this->attribute = 'grado de instrucción';
                            return false;
                        }
                    }
                }
            } elseif (($this->group_by_clasification == 'position') && ($this->group_by_years != null)) {
                if (
                    is_array($payrollScale['payroll_scale_requirements'])
                    && count($payrollScale['payroll_scale_requirements']) > 0
                ) {
                    foreach ($payrollScale['payroll_scale_requirements'] as $field) {
                        if (is_null($field['scale_years_minimum'])) {
                            $this->attribute = 'cantidad minima de años';
                            return false;
                        };
                        if (is_null($field['scale_years_maximum'])) {
                            $this->attribute = 'cantidad maxima de años';
                            return false;
                        }
                        if (is_null($field['clasificable_id'])) {
                            $this->attribute = 'cargo';
                            return false;
                        }
                    }
                } elseif (count($payrollScale['payroll_scale_requirements']) == 0) {
                    $this->attribute = 'requerimientos';
                    return false;
                }
            } elseif (($this->group_by_clasification == 'instruction_degree') && ($this->group_by_years != null)) {
                if (
                    is_array($payrollScale['payroll_scale_requirements'])
                    && count($payrollScale['payroll_scale_requirements']) > 0
                ) {
                    foreach ($payrollScale['payroll_scale_requirements'] as $field) {
                        if (is_null($field['scale_years_minimum'])) {
                            $this->attribute = 'cantidad minima de años';
                            return false;
                        };
                        if (is_null($field['scale_years_maximum'])) {
                            $this->attribute = 'cantidad maxima de años';
                            return false;
                        }
                        if (is_null($field['clasificable_id'])) {
                            $this->attribute = 'grado de instrucción';
                            return false;
                        }
                    }
                } elseif (count($payrollScale['payroll_scale_requirements']) == 0) {
                    $this->attribute = 'requerimientos';
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Obtiene el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'El campo ' . $this->attribute . ' en las escalas o niveles del escalafón salarial es requerido.';
    }
}
