<?php

namespace Modules\Budget\Models;

use App\Models\DocumentStatus as BaseDocumentStatus;

/**
 * @class DocumentStatus
 * @brief Modelo que extiende las funcionalidades del modelo base DocumentStatus
 *
 * Modelo que extiende las funcionalidades del modelo base DocumentStatus
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DocumentStatus extends BaseDocumentStatus
{
    /**
     * Establece la relación con formulaciones presupuestarias asociadas a estatus de documentos
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetSubSpecificFormulations()
    {
        return $this->hasMany(BudgetSubSpecificFormulation::class);
    }

    /**
     * Establece la relación con modificaciones presupuestarias asociadas a estatus de documentos
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetModifications()
    {
        return $this->hasMany(BudgetModification::class);
    }

    /**
     * Obtiene la relación con los compromisos presupuestarios asociados a estatus de documentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCompromise()
    {
        return $this->hasMany(BudgetCompromise::class);
    }
}
