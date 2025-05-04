<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @class OrganismScope
 * @brief Scope general para filtrar datos por organismos
 *
 * Filtra los datos a consultar por el organismo al cual pertenece el usuario
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class OrganismScope implements Scope
{
    /**
     * Aplica filtro de uso global en las consultas
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && auth()->user()->organism_id !== null) {
            $user = auth()->user();
            $builder->where('institution_id', $user->organism_id);
        }
    }
}
