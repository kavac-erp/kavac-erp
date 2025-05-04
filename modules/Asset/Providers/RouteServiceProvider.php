<?php

namespace Modules\Asset\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * @class AssetServiceProvider
 * @brief Gestiona los servicios de rutas del módulo de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * El namespace del controlador del módulo de bienes
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Asset\Http\Controllers';

    /**
     * Se invoca antes de las rutas que fueron registradas.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define las rutas del módulo de bienes
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define las rutas "web" del módulo de bienes
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Asset', '/Routes/web.php'));
    }

    /**
     * Define las rutas de API del módulo de bienes
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Asset', '/Routes/api.php'));
    }
}
