<?php

namespace Modules\Asset\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class RequiredItem
 * @brief Reglas de validación para los campos requeridos en el registro
 *
 * Gestiona las reglas de validación de campos requeridos en el registro
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RequiredItem implements Rule
{
    /**
     * Define si el campo es requerido
     *
     * @var boolean $required
     */
    protected $required;

    /**
     * Crea una nueva instancia de la clase
     *
     * @return void
     */
    public function __construct($required = true)
    {
        $this->required = $required;
    }

    /**
     * Determina si la regla de validación se cumple
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value = '')
    {
        if ($this->required == true) {
            return ($value != '');
        } else {
            return true;
        }
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'El campo :attribute es obligatorio.';
    }
}
