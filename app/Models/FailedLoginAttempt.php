<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class FailedLoginAttempt
 * @brief Datos de los intentos de acceso fallidos
 *
 * Gestiona el modelo de datos para los intentos de acceso fallidos
 *
 * @property  string  $user_id
 * @property  string  $username
 * @property  string  $ip
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FailedLoginAttempt extends Model implements Auditable
{
    use AuditableTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id', 'username', 'ip',
    ];

    /**
     * Registra los datos del intento fallido de autenticación en el sistema
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string           $username    Nombre del usuario utilizado para él acceso al sistema
     * @param      string           $ip          Dirección IP desde donde se intentó acceder al sistema
     * @param      User|null        $user        Objeto que contiene información del usuario
     *
     * @return     FailedLoginAttempt           Devuelve un objeto con los datos del intento fallido
     */
    public static function record($username, $ip, $user = null)
    {
        return static::create([
            'user_id' => is_null($user) ? null : $user->id,
            'username' => $username,
            'ip' => $ip,
        ]);
    }

    /**
     * Método que obtiene la información de intento fallido de acceso de un usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
