<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\Estate;

/**
 * @class UniqueEstateCode
 * @brief Reglas de validación para los registros de Estados
 *
 * Gestiona las reglas de validación para los registros de Estados
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UniqueEstateCode implements Rule, DataAwareRule
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
        $estate = Estate::where(['country_id' => $this->data['country_id'], 'code' => $value])->first();

        return !$estate;
    }

    /**
     * Obtener el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Él código del Estado ya existe para el Pais seleccionado.';
    }
}
