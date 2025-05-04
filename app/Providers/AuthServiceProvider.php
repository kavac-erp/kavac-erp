<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * @class AuthServiceProvider
 * @brief Proveedor de servicios de autenticación
 *
 * Gestiona los proveedores de servicios de autenticación
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Las asignaciones de políticas para la aplicación.
     *
     * @var array $policies
     */
    protected $policies = [
        //'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Registra cualquier servicio de autenticación/autorización.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
