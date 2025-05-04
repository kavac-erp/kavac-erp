<?php

namespace Modules\Asset\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class AcquisitionYear
 * @brief Reglas de validación para el año de adquisición de un bien
 *
 * Gestiona las reglas de validación de el año de adquisición de un bien
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AcquisitionYear implements Rule
{
    /**
     * Define el año maximo de adquisición
     *
     * @var integer $year
     */
    protected $year;

    /**
     * Crea una nueva instancia de la clase
     *
     * @return void
     */
    public function __construct($year)
    {
        $this->year = $year;
    }

    /**
     * Determina si la regla de validación se cumple
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $date = strtotime($value);
        $year = date("Y", $date);
        return $year <= $this->year;
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'El campo :attribute no debe ser superior al año actual. El año actual es: ' . $this->year;
    }
}
