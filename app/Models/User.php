<?php

namespace App\Models;

use Illuminate\Support\Facades\Date;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use App\Roles\Traits\HasRoleAndPermission;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @class User
 * @brief Datos de Usuarios
 *
 * Gestiona el modelo de datos para las Usuarios
 *
 * @property string  $id
 * @property string  $name
 * @property string  $email
 * @property string  $password
 * @property string  $username
 * @property boolean $lock_screen
 * @property int     $time_lock
 * @property Date    $blocked_at
 * @property Date    $last_login
 * @property string  $remember_token
 * @property object  $profile
 * @property boolean $active
 * @property Date|null $email_verified_at
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class User extends Authenticatable implements Auditable, MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoleAndPermission;
    use AuditableTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'lock_screen', 'time_lock', 'blocked_at'
    ];

    /**
     * Oculta los campos de contraseña y token de recordar autenticación de usuario
     *
     * @var array $hidden
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Establece el tipo de dato para la columna "email_verified_at"
     *
     * @var array $casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'last_login', 'blocked_at'];

    /**
     * Metodo que obtiene los intentos fallidos de inicio de sesión
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function failedLoginAttempts()
    {
        return $this->hasMany(FailedLoginAttempt::class);
    }

    /**
     * Método que obtiene el perfil de un usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Método que obtiene la configuración de notificaciones de un usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notificationSettings()
    {
        return $this->belongsToMany(NotificationSetting::class)->withPivot('type')->withTimestamps();
    }

    /**
     * Envía la notificación por correo para el reestablecimiento de la contraseña
     *
     * @param     string    $token    Token de la URL para el acceso al formulario de reestablecimiento de contraseña
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        if (!config('auth.active_directory.enabled', false)) {
            $this->notify(new ResetPasswordNotification($token));
        }
    }

    /**
     * Envía la notificación de verificación de usuario para poder acceder a la aplicación
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if (!config('auth.active_directory.enabled', false)) {
            $this->notify(new VerifyEmailNotification());
        }
    }

    /**
     * Obtiene el identificador del usuario
     *
     * @return    string|integer           Identificador del usuario
     */
    public function getIdentifier()
    {
        return $this->getKey();
    }
}
