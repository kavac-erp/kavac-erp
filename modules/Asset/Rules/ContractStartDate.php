<?php

namespace Modules\Asset\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class ContractStartDate
 * @brief Regla para validar la fecha de inicio de un contrato
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ContractStartDate implements Rule
{
    /**
     * Valor de la fecha del contrato
     *
     * @var string    $date_value
     */
    private $date_value;

    /**
     * Nombre del campo a validar
     *
     * @var string    $field_name
     */
    private $field_name;

    /**
     * Mensaje de validación
     *
     * @var string    $message
     */
    private $message;

    /**
     * Crea una nueva instancia de la regla
     *
     * @return void
     */
    public function __construct($field_name, $date_value)
    {
        $this->field_name = $field_name;
        $this->date_value = $date_value;
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
        if ($this->field_name == 'contract_end_date' && $value > $this->date_value) {
            $this->message = 'La fecha de inicio de contrato no puede ser posterior a la fecha de fin de contrato.';
            return false;
        }

        if ($this->field_name == 'acquisition_date' && $value < $this->date_value) {
            $this->message = 'La fecha de inicio de contrato no puede ser anterior a la fecha de adquisición.';
            return false;
        }

        return true;
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
