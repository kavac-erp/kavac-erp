<?php

namespace App\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use App\Roles\Contracts\PermissionHasRelations as PermissionHasRelationsContract;
use App\Roles\Traits\PermissionHasRelations;
use App\Roles\Traits\Slugable;

/**
 * @class Permission
 * @brief Modelo para la gestión de permisos
 *
 * Gestiona información sobre los permisos de acceso
 *
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $model
 * @property string $model_prefix
 * @property string $slug_alt
 * @property string $short_description
 *
 * @author ultraware\roles <a href="https://github.com/ultraware/roles.git">Ultraware\Roles</a>
 */
class Permission extends Model implements PermissionHasRelationsContract
{
    use Slugable;
    use PermissionHasRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description', 'model', 'model_prefix', 'slug_alt', 'short_description'];

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
