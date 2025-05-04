<?php

namespace Modules\Warehouse\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

/**
 * @class WarehouseServiceProvider
 * @brief Proveedor de servicios para el módulo de Almacén
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseServiceProvider extends ServiceProvider
{
    /**
     * Nombre del módulo
     *
     * @var string $moduleName
     */
    protected $moduleName = 'Warehouse';

    /**
     * Nombre en minúsculas del módulo
     *
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'warehouse';

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

        Validator::extend(
            'titlecase',
            function ($attribute, $value, $parameters, $validator) {
                return strtoupper($value) === $value;
            }
        );
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
        $this->publishes(
            [
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
            ],
            'config'
        );
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

        $this->publishes(
            [
            $sourcePath => $viewPath
            ],
            [
            'views', $this->moduleNameLower . '-module-views'
            ]
        );

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
