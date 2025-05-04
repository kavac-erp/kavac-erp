<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * @class TrustHosts
 * @brief Gestiona los middleware para los servidores de confianza
 *
 * Gestiona los middleware para los servidores de confianza
 */
class TrustHosts extends Middleware
{
    /**
     * Obtiene los patrones de servidores en los que se debe confiar.
     *
     * @return array
     */
    public function hosts()
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}
