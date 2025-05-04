<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * @class RouteServiceProvider
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * El namespace del módulo que se debe asumir al generar las URL.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\ProjectTracking\Http\Controllers';

    /**
     * Se invoca antes de que las rutas sean registradas.
     *
     * Registra cualquier enlace del modelo o filtros basados en patrones.
     *
     * @method boot
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define las rutas del módulo.
     *
     * @method map
     *
     * @return void     [descripción de los datos devueltos]
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define las rutas "web" del módulo.
     *
     * Todas estas rutas reciben el estado de sesión, protección CSRF, etc.
     *
     * @method mapWebRoutes
     *
     * @return void     [descripción de los datos devueltos]
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('ProjectTracking', '/Routes/web.php'));
    }

    /**
     * Define las rutas "api" del módulo.
     *
     * Estas rutas son típicamente sin estado.
     *
     * @method mapWebRoutes
     *
     * @return void     [descripción de los datos devueltos]
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('ProjectTracking', '/Routes/api.php'));
    }
}
