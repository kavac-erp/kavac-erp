<?php

namespace Modules\Payroll\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * @class PayrollTimeSheetPendingConceptsRequired
 * @brief Reglas de validación para las hojas de tiempo pendiente
 *
 * Gestiona las reglas de validación de las hojas de tiempo pendiente
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetPendingConceptsRequired implements Rule
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
        $countData = 0;
        $countConcepts = 0;
        foreach ((array)$value as $key => $tData) {
            if (str_contains($key, 'total-')) {
                if ($tData > 0) {
                    $countData++;
                }
            }

            if (str_contains($key, 'Conceptos')) {
                if (!empty($tData)) {
                    $countConcepts++;
                }
            }
        }

        if ($countData > 0 && $countConcepts > 0 && $countData == $countConcepts) {
            $hasTimeSheetData = true;
        }

        return $hasTimeSheetData;
    }

    /**
     * Obtiene el mensaje de error de validación.
     *
     * @return string|array
     */
    public function message()
    {
        return ['Los conceptos de la hoja de tiempo son obligatorios', 'asd'];
    }
}
