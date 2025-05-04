<?php

namespace Modules\Accounting\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * @class RouteServiceProvider
 * @brief Clase que gestiona los servicios de rutas del módulo de Contabilidad
 *
 * Gestiona los servicios de rutas del módulo de Contabilidad
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Namespace de las rutas del controlador
     *
     * @var string $moduleNamespace
     */
    protected $moduleNamespace = 'Modules\Accounting\Http\Controllers';

    /**
     * Registra cualquier binding o filtro
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define las rutas para la aplicación
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define las rutas "web" de la aplicación
     *
     * Todas estas rutas reciben el estatus de sesión, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Accounting', '/Routes/web.php'));
    }

    /**
     * Define las rutas "api" para la aplicación
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Accounting', '/Routes/api.php'));
    }
}
