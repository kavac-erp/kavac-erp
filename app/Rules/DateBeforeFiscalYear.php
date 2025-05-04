<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\FiscalYear;

/**
 * @class DateBeforeFiscalYear
 * @brief Reglas de validación
 *
 * Gestiona las reglas de validación
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DateBeforeFiscalYear implements Rule
{
    /**
     * Nombre del campo al que se le aplica la validación
     *
     * @var    integer $attribute
     */
    protected $attribute;

    /**
     * Recibe por parámetro el nombre del campo al que se aplica la validación
     *
     * @param integer   $attribute    Edad de la persona
     *
     * @return void
     */
    public function __construct($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Determinar si la regla de validación es correcta.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $currentFiscalYear = FiscalYear::select('year')->where([
            'active' => true, 'closed' => false
        ])->orderBy('year', 'desc')->first();
        if (date('Y') > $currentFiscalYear->year) {
            $date = date($currentFiscalYear->year . '-12-31');
        } else {
            $date = date('Y-m-d');
        }

        return $value <= $date;
    }

    /**
     * Obtiene el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        $currentFiscalYear = FiscalYear::select('year')->where([
            'active' => true, 'closed' => false
        ])->orderBy('year', 'desc')->first();
        if (date('Y') > $currentFiscalYear->year) {
            $date = date('31/12/' . $currentFiscalYear->year);
        } else {
            $date = date('d/m/Y');
        }

        return __('La fecha del campo ' . $this->attribute . ' debe ser menor o igual al ' . $date . '.');
    }
}
