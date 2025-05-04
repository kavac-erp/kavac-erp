<?php

namespace Modules\Asset\Rules\Setting;

use Illuminate\Contracts\Validation\Rule;
use Modules\Asset\Models\AssetSpecificCategory;

/**
 * @class AssetSpecificCategoryUnique
 * @brief Regla para validar la unicidad de una categoría específica de bien
 *
 * @author  Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSpecificCategoryUnique implements Rule
{
    /**
     * Identificador unico de subcategoría
     *
     * @var    string    $asset_subcategory_id
     */
    protected $asset_subcategory_id;

    /**
     * Código de la subcategoría
     *
     * @var    string    $code
     */
    protected $code;

    /**
     * Crea una nueva instancia de la regla
     *
     * @return void
     */
    public function __construct($asset_subcategory_id, $code)
    {
        $this->asset_subcategory_id = $asset_subcategory_id;
        $this->code  = $code;
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
        $assetCategories = AssetSpecificCategory::where('asset_subcategory_id', $this->asset_subcategory_id)
                         ->where('code', $this->code)
                         ->where('id', '<>', $value)
                         ->get();
        return count($assetCategories) > 0
                ? false
                : true;
    }

    /**
     * Obtiene el mensaje de validación.
     *
     * @return string    Devuelve una cadena de texto con el mensaje de error si la validación no es exitosa
     */
    public function message()
    {
        return 'La dupla subcategoría y código de la categoría especifica ya se encuentra registrada';
    }
}
