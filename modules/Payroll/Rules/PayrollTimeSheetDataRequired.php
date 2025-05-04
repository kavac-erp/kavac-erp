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
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetDataRequired implements Rule
{
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
