<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * @class AppServiceProvider
 * @brief Proveedor de servicios de las rutas
 *
 * Gestiona los proveedores de servicios de las rutas
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * La ruta a la ruta "inicio" para su aplicación.
     *
     * Esto es utilizado por la autenticación de Laravel para redirigir a los usuarios después de iniciar sesión.
     */
    public const HOME = '/';

    /**
     * El espacio de nombres del controlador para la aplicación..
     *
     * Cuando esté presente, las declaraciones de ruta del controlador tendrán automáticamente el prefijo de este espacio de nombres.
     *
     * @var string|null $namespace
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Defina los enlaces de su modelo de ruta, los filtros de patrón, etc..
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            // Rutas generales para la gestión de módulos
            Route::middleware('web')->namespace($this->namespace)->group(base_path('routes/module.php'));

            // Rutas para la gestión esclusiva del administrador de la aplicación
            Route::middleware('web')->namespace($this->namespace)->group(base_path('routes/admin.php'));

            if ((config('app.debug') || config('app.debug') === "true") && env('TEST_URL', false)) {
                // Rutas para pruebas de desarrollo
                Route::middleware('web')->namespace($this->namespace)->group(base_path('routes/test.php'));
            }
        });
    }

    /**
     * Configurar los limitadores de velocidad para la aplicación.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
    }
}
