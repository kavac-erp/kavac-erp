<?php

namespace Modules\DigitalSignature\Models;

use App\Models\User as BaseUser;

/**
 * @class User
 * @brief Extiende de la clase User de la aplicación base
 *
 * Clase que hereda de la clase User de la aplicación base
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
 *
 * @property Signprofile $signprofiles datos de la firma del usuario
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class User extends BaseUser
{
    /**
     * Establece la relación con la firma del usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function signprofiles()
    {
        return $this->hasOne(Signprofile::class);
    }
}
