<?php

/** Proveedores de servicios generales del sistema */

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
     * Register any application services.
     *
     * @method  register
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @method  boot
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(255);
        Paginator::useBootstrap();

        if (!app()->runningInConsole()) {
            $module = new Module();
            /** Solo ejecuta esta instrucción si no se esta ejecutando en consola de comandos */
            foreach (NotificationSetting::all() as $notifySetting) {
                if (!is_null($notifySetting->module) && $module->isDisabled($notifySetting->module)) {
                    continue;
                }

                ($notifySetting->model)::observe(ModelObserver::class);
            }
        }
    }
}
