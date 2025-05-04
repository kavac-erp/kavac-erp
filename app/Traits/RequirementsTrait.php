<?php

namespace App\Traits;

/**
 * @trait RequirementsTrait
 * @brief Trait para la gestión de requerimientos previos a una funcionalidad de la aplicación
 *
 * Gestión de requerimientos de funcionalidades de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
trait RequirementsTrait
{
    /**
     * Verifica si una funcionalidad de la aplicación tiene los requermientos necesarios para la gestión de datos
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     array      $models    Arreglo de modelos requeridos por la funcionalidad y los permisos necesarios
     *                                  para administrar los datos si es necesario
     *
     * @return    boolean               Devuelve verdadero si la funcionalidad cumple con los requerimientos necesarios
     *                                  para la gestión de información, de lo contrario devuelve falso
     */
    public function verifyRequirements($models)
    {
        if (count($models) > 0) {
            foreach ($models as $model => $permission) {
                if ($model::all()->count() === 0) {
                    return false;
                }
            }
        }
        return true;
    }
}
