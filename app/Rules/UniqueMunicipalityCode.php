<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\Municipality;

/**
 * @class UniqueMunicipalityCode
 * @brief Reglas de validación para los registros de Municipios
 *
 * Gestiona las reglas de validación para los registros de Municipios
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UniqueMunicipalityCode implements Rule, DataAwareRule
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
        $municipality = Municipality::where(['estate_id' => $this->data['estate_id'], 'code' => $value])->first();

        return !$municipality;
    }

    /**
     * Obtener el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Él código del Municipio ya existe para el Pais y Estado seleccionado.';
    }
}
