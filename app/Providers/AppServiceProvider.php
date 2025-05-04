<?php

namespace App\Providers;

use App\Observers\ModelObserver;
use App\Models\NotificationSetting;
use Nwidart\Modules\Facades\Module;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * @class AppServiceProvider
 * @brief Proveedor de servicios de la aplicación
 *
 * Gestiona los proveedores de servicios de la aplicación
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra cualquier servicio de la aplicación
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Ejecuta los servicios de la aplicación
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(255);
        Paginator::useBootstrap();

        if (!app()->runningInConsole()) {
            // Solo ejecuta esta instrucción si no se esta ejecutando en consola de comandos
            foreach (NotificationSetting::all() as $notifySetting) {
                if (!is_null($notifySetting->module) && Module::isDisabled($notifySetting->module)) {
                    continue;
                }

                ($notifySetting->model)::observe(ModelObserver::class);
            }
        }
    }
}
