<?php

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Modules\Payroll\Models\PayrollStaff;
use Illuminate\Contracts\Validation\Rule;
use Modules\Payroll\Models\PayrollPosition;
use Illuminate\Contracts\Validation\DataAwareRule;

/**
 * @class PayrollPositionRestriction
 * @brief Gestiona las reglas de validación y la validación para determinar si cumple o no con el requerimiento
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPositionRestriction implements Rule, DataAwareRule
{
    /**
     * Datos bajo validación
     *
     * @var array $data
     */
    protected array $data;

    /**
     * Mensajes de la validación
     *
     * @var string $message
     */
    protected string $message;

    /**
     * Establece los datos para la validación
     *
     * @param array $data Arreglo con los datos
     *
     * @return static
     */
    public function setData($data)
    {
        $this->data = Arr::collapse($data);

        return $this;
    }

    /**
     * Determina si pasa la regla de validación.
     *
     * @param  string  $attribute   Nombre del atributo
     * @param  mixed   $value       Valor del atributo a evaluar
     *
     * @return bool    Devuelve verdadero si la regla se cumple de lo contrario devuelve falso
     */
    public function passes($attribute, $value)
    {
        $position = PayrollPosition::withCount(['payrollEmployments' => function ($query) {
            $query->where('payroll_employment_payroll_position.active', true);
        }])->where('id', $value)->first();

        if (!$position) {
            $this->message = 'El cargo no existe.';

            return false;
        }

        $payrollStaff = PayrollStaff::withOnly(['payrollEmploymentNoAppends' => function ($query) use ($value) {
            $query->select('id', 'payroll_staff_id');
            $query->with(['payrollPositions' => function ($query) use ($value) {
                $query->where('payroll_position_id', $value);
            }]);
        }])->where('id_number', $this->data['cedula_de_identidad'])->first();

        /* Verifica si el trabajador tiene el mismo cargo que viene en el excel */
        if ($payrollStaff?->payrollEmploymentNoAppends?->payrollPositions?->where('id', $value)->count() > 0) {
            return true;
        }

        $numberPositionsAssigned = $position->number_positions_assigned
            ? $position->number_positions_assigned : 0;

        $this->message = 'No hay disponibilidad de asignación para el cargo seleccionado.';

        return ($numberPositionsAssigned - $position->payroll_employments_count > 0);
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message()
    {
        return $this->message;
    }
}
