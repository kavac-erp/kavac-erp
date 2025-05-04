<?php

/** Proveedores de servicios generales del sistema */

namespace App\Providers;

//use Illuminate\Support\Facades\Gate;
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
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Registre cualquier servicio de autenticación/autorización.
     *
     * @method  boot
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
