<?php

namespace Modules\Asset\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class DateExtension
 * @brief Reglas de validación para las prorrogas de entrega de equipos
 *
 * Gestiona las reglas de validación de las fechas de prorroga de entrega de equipos
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DateExtension implements Rule
{
    /**
     * Define el numero de dias adicionales permitidos para la entrega
     *
     * @var integer $days
     */
    protected $days;

    /**
     * Define la fecha de entrega original
     *
     * @var string $date
     */
    protected $date;

    /**
     * Crea una nueva instancia de la clase
     *
     * @return void
     */
    public function __construct($date, $days = '0')
    {
        $this->days = $days;
        $this->date = date('d-m-Y', strtotime($date));
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
        $days_extension = '+ ' . $this->days . ' days';
        return ((date('d-m-Y', strtotime($this->date . $days_extension)) >= date('d-m-Y', strtotime($value))) &&
            (date('d-m-Y', strtotime($value)) >= date('d-m-Y', strtotime($this->date))));
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'El campo :attribute excede el plazo permitido. Maximo: ' . $this->days . ' dias adicionales';
    }
}
