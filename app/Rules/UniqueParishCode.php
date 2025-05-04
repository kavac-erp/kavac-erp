<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\Parish;

/**
 * @class UniqueParishCode
 * @brief Reglas de validación para los registros de Parroquias
 *
 * Gestiona las reglas de validación para los registros de Parroquias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UniqueParishCode implements Rule, DataAwareRule
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
        $parish = Parish::where(['municipality_id' => $this->data['municipality_id'], 'code' => $value])->first();

        return !$parish;
    }

    /**
     * Obtener el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return 'Él código de la Parroquia ya existe para el Pais, Estado y Municipio seleccionado.';
    }
}
