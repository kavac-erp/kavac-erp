<?php

namespace Modules\Sale\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * @class RouteServiceProvider
 * @brief Gestiona los servicios de rutas del módulo de comercialización
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * El namespace del controlador del módulo
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Sale\Http\Controllers';

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
     * Define las rutas "web" del módulo
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Sale', '/Routes/web.php'));
    }

    /**
     * Define las rutas de API del módulo
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Sale', '/Routes/api.php'));
    }
}
