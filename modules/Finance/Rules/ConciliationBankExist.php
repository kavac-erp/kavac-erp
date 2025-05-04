<?php

namespace Modules\Finance\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Finance\Models\FinanceSettingBankReconciliationFiles;

/**
 * @class ConciliationBankExist
 * @brief Regla de validación para determinar si existe una configuración de conciliación para un banco
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ConciliationBankExist implements Rule
{
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
     * Determina si pasa la regla de validación.
     *
     * @param  string  $attribute   Nombre del atributo
     * @param  mixed   $value       Valor del atributo a evaluar
     *
     * @return bool    Devuelve verdadero si la regla se cumple de lo contrario devuelve falso
     */
    public function passes($attribute, $value)
    {
        return !FinanceSettingBankReconciliationFiles::where('bank_id', $value)->first();
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message()
    {
        return 'Ya existe una configuración de conciliación para el banco seleccionado.';
    }
}
