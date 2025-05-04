<?php

namespace Modules\Payroll\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class PayrollSalaryScales
 * @brief Reglas de validación para los escalafones salariales
 *
 * Gestiona las reglas de validación de los escalafones salariales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryScales implements Rule
{
    /**
     * Define el mensaje de validación
     *
     * @var string $message
     */
    protected $message;

    /**
     * Crea una nueva instancia de la regla.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        $this->message = null;
    }

    /**
     * Determina si la regla de validación es correcta.
     *
     * @param  string  $attribute Atributo a verificar
     * @param  mixed  $value     Valor del atributo a verificar
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ((array) $value as $payrollSalaryScale) {
            if (is_null($payrollSalaryScale['value'])) {
                $this->message = 'son obligatorios.';
                return false;
            } elseif ((float)$payrollSalaryScale['value'] < 0) {
                $this->message = 'deben ser positivos.';
                return false;
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
        return 'Los valores del tabulador salarial ' . $this->message;
    }
}
