<?php

namespace Modules\Payroll\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class PayrollTimeSheetDataRequired
 * @brief Reglas de validación para las hojas de tiempo
 *
 * Gestiona las reglas de validación de las hojas de tiempo
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class PayrollTimeSheetDataRequired implements Rule
{
    /**
     * Determina si la regla de validación es correcta.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $hasTimeSheetData = false;
        foreach ((array)$value as $tData) {
            if ($tData != 0) {
                $hasTimeSheetData = true;
                break;
            }
        }

        return $hasTimeSheetData;
    }

    /**
     * Obtiene el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Debe llenar al menos un campo de la hoja de tiempo.';
    }
}
