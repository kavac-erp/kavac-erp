<?php

/** Proveedores de servicios generales del sistema */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

/**
 * @class BroadcastServiceProvider
 * @brief Proveedor de servicios de los canales de transmisión
 *
 * Gestiona los proveedores de servicios de los canales de transmisión
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap cualquier servicio de aplicación.
     *
     * @method  boot
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
