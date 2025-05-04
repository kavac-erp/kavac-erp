<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\Currency;

/**
 * @class UniqueCurrency
 * @brief Reglas de validación para los registros de monedas
 *
 * Gestiona las reglas de validación para los registros de monedas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UniqueCurrency implements Rule, DataAwareRule
{
    /**
     * Arreglo con todos los datos en validación.
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Establecer los datos bajo validación.
     *
     * @param  array  $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Determinar si la regla de validación pasa.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $currency = Currency::where('country_id', $this->data['country_id'])
                            ->where('symbol', $this->data['symbol'])
                            ->orWhere('name', $this->data['name'])->first();
        return !$currency;
    }

    /**
     * Obtener el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Ya existe una moneda para el Pais seleccionado con el mismo nombre o símbolo.';
    }
}
