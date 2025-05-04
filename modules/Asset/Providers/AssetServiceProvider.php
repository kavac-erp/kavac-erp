<?php

namespace Modules\Asset\Providers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

/**
 * @class AssetReport
 * @brief Gestiona los proveedores de servicios del módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetServiceProvider extends ServiceProvider
{
    /**
     * Nombre del módulo
     *
     * @var string $moduleName
     */
    protected $moduleName = 'Asset';

    /**
     * Nombre del módulo en minúsculas
     *
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'asset';

    /**
     * Carga los eventos del módulo.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        /* Se ejecuta antes de que se procese el trabajo */

        Queue::before(function (JobProcessing $event) {
            // TODO:
            // echo "before";
            // Acceder al reporte del trabajo y cambiar estatus de
            // pendiente por ejecutar a en proceso
        });

        /* Se ejecuta despues de que se procesa el trabajo */

        Queue::after(function (JobProcessed $event) {
            // TODO:
            // echo "after";
            // Acceder al reporte del trabajo y cambiar estatus de
            // en proceso a terminado
            // Lanzar evento descrito en el trabajo (abrir al finalizar o forzar descarga)
        });
    }

    /**
     * Registra los proveedores de servicios
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Registra la configuración
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    /**
     * Registra las vistas.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Registra las traducciones.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Registra un directorio adicional para los factories
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            $this->loadFactoriesFrom(module_path($this->moduleName, 'Database/factories'));
        }
    }

    /**
     * Obtiene los proveedores de servicios por proveedor
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Obtiene el path de las vistas de la configuración en BD
     *
     * @return array
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
