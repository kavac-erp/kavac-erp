<?php

namespace App\Roles\Models;

use App\Models\User;
use App\Roles\Traits\Slugable;
use App\Roles\Traits\RoleHasRelations;
use Illuminate\Database\Eloquent\Model;
use App\Roles\Contracts\RoleHasRelations as RoleHasRelationsContract;

/**
 * @class Role
 * @brief Modelo para la gestión de roles
 *
 * Gestiona información sobre los roles de acceso
 *
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int    $level
 * @property User   $users
 * @property Permission $permissions
 *
 * @author ultraware\roles <a href="https://github.com/ultraware/roles.git">Ultraware\Roles</a>
 */
class Role extends Model implements RoleHasRelationsContract
{
    use Slugable;
    use RoleHasRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description', 'level'];

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($connection = config('roles.connection')) {
            $this->connection = $connection;
        }
    }
}
