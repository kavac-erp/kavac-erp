<?php

namespace Modules\Accounting\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Console\Command\DeleteEntry;
use Modules\Accounting\Console\Command\EntryDocumentStatusAprove;
use Modules\Accounting\Console\Command\CreateReverceAccountingAccountforPayOrders;

/**
 * @class AccountingServiceProvider
 * @brief Clase que gestiona los proveedores de servicios del módulo de Contabilidad
 *
 * Gestiona los proveedores de servicios del módulo de Contabilidad
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingServiceProvider extends ServiceProvider
{
    /**
     * Nombre del modulo
     *
     * @var string $moduleName
     */
    protected $moduleName = 'Accounting';

    /**
     * Nombre del modulo en minisculas
     *
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'accounting';

    /**
     * Inicializa los eventos del módulo de contabilidad
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
        $this->registerCommands();
    }

    /**
     * Registra el proveedor de servicio
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
     * Registra las vistas
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
     * Registra las traducciones
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
     * Registra un directorio adicional para los factory
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
     * Obtiene los proveedores de servicios
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Obtiene las rutas de las carpetas de vistas
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

    /**
     * Registra los comandos del módulo de contabilidad
     *
     * @return void
     */
    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            // Registrar comandos solo si se está ejecutando en la consola
            $this->commands([
                CreateReverceAccountingAccountforPayOrders::class,
                EntryDocumentStatusAprove::class,
                DeleteEntry::class,
            ]);
        }
    }
}
