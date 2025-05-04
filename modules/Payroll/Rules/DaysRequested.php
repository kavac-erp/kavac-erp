<?php

namespace Modules\Payroll\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

/**
 * @class DaysRequested
 * @brief Gestiona las reglas de validación y la validación para determinar si cumple o no con el requerimiento
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DaysRequested implements Rule, DataAwareRule
{
    /**
     * Datos bajo validación
     *
     * @var array $data
     */
    protected array $data;

    /**
     * Crea una nueva instancia de la regla
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Establece los datos para la validación
     *
     * @param  array  $data Arreglo con los datos
     *
     * @return $this
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
        /** Obtiene los dias entre la fecha de inicio y la fecha de fin */
        $daysBetweenStartDateAndEndDate = $this->getWeekdayCount($this->data['end_date'], $this->data['start_date']);

        return $value == $daysBetweenStartDateAndEndDate;
    }


    /**
     * Obtiene el numero de dias entre la fecha de inicio y la fecha de fin.
     * Solo contempla los dias lunes, martes, miercoles, jueves y viernes
     *
     * @param  \Carbon\Carbon $startDate   Fecha de inicio
     * @param  \Carbon\Carbon $endDate     Fecha de fin
     *
     * @return integer
     */
    public function getWeekdayCount($endDate, $startDate)
    {
        $count = 0;

        while ($startDate <= $endDate) {
            if ($startDate->format('N') < 6) { // Monday to Friday
                $count++;
            }
            $startDate->modify('+1 day');
        }

        return $count;
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message()
    {
        return 'El número de dias solicitados debe ser igual al número de dias entre la fecha de inicio y la fecha de culminación del periodo vacacional.';
    }
}
