<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\City;

/**
 * @class UniqueCityName
 * @brief Reglas de validación para los registros de Ciudades
 *
 * Gestiona las reglas de validación para los registros de Ciudades
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UniqueCityName implements Rule, DataAwareRule
{
    /**
     * Arreglo de todos los datos en validación.
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
     * Determinar si la regla de validación es correcta.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $city = City::where(['estate_id' => $this->data['estate_id'], 'name' => $value])->first();
        return !$city;
    }

    /**
     * Obtiene el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Ya existe una Ciudad con el mismo nombre.';
    }
}
